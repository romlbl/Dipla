<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Fichier "non tabulaire" -> texte (Cloudflare toMarkdown) -> produits (LLM, par paquets en parallèle).
 * Sortie au même format que CatalogReader : ['headers' => [...], 'rows' => [...]].
 */
class CatalogAiExtractor
{
    private const MODEL = '@cf/meta/llama-3.3-70b-instruct-fp8-fast';
    // private const MODEL = '@cf/meta/llama-3.1-8b-instruct';
    private const CHUNK_CHARS = 3500; // plus petit = sortie jamais coupée
    private const MAX_CHUNKS = 20;
    private const PARALLEL = 4;
    private const PLAIN = ['txt', 'md', 'csv', 'tsv', 'xml', 'json']; // déjà du texte : pas besoin de convertir

    private const HEADERS = ['title', 'price', 'description', 'type', 'keywords', 'images'];

    private const PROMPT = <<<'TXT'
        Extract EVERY product or service from the text. Do not skip any. Return ONLY a JSON object: {"items": [...]}.
        Each item: {"title": string, "price": number or null (no currency; null if missing or variable), "description": string, "type": "produit" or "service", "keywords": "comma, separated, max 5", "images": [image URLs]}.
        Image URLs: copy exactly as written, only those belonging to THAT product, never invent one. Empty array if none.
        Never invent data. Ignore text that is not a product or service.
        TXT;

    public function extract(string $path, string $ext): array
    {
        $text = in_array($ext, self::PLAIN, true)
            ? $this->utf8((string) file_get_contents($path))
            : $this->toMarkdown($path, $ext);

        $text = trim($text);
        if ($text === '') {
            throw new RuntimeException('Aucun texte lisible dans ce fichier (image floue ou fichier vide ?).');
        }

        $chunks = $this->chunk($text);
        if (count($chunks) > self::MAX_CHUNKS) {
            throw new RuntimeException('Fichier trop long pour la lecture IA. Utilisez un CSV ou Excel (sans limite) ou découpez le fichier.');
        }

        $rows = $this->askModel($chunks);

        if ($rows === []) {
            throw new RuntimeException("L'IA n'a trouvé aucun produit ni service dans ce fichier.");
        }

        return ['headers' => self::HEADERS, 'rows' => $rows];
    }

    private function toMarkdown(string $path, string $ext): string
    {
        try {
            $response = Http::withToken(config('services.cloudflare.token'))
                ->timeout(90)
                ->attach('files', (string) file_get_contents($path), 'catalogue.'.$ext)
                ->post($this->base().'/ai/tomarkdown');
        } catch (\Throwable $e) {
            report($e);
            throw new RuntimeException('Service de lecture IA indisponible. Réessayez dans un instant.');
        }

        $result = $response->json('result.0');

        if (! $response->successful() || ! $result || ($result['format'] ?? '') === 'error') {
            Log::warning('toMarkdown échoué', ['status' => $response->status(), 'body' => $response->body()]);
            throw new RuntimeException("Le format « .{$ext} » n'a pas pu être lu.");
        }

        return (string) ($result['data'] ?? '');
    }

    /** Coupe sur des fins de ligne pour ne pas couper un produit en deux. */
    private function chunk(string $text): array
    {
        $chunks = [];
        $current = '';

        foreach (preg_split('/\R/u', $text) as $line) {
            if ($current !== '' && mb_strlen($current) + mb_strlen($line) > self::CHUNK_CHARS) {
                $chunks[] = $current;
                $current = '';
            }
            $current .= $line."\n";
        }

        if (trim($current) !== '') {
            $chunks[] = $current;
        }

        return $chunks;
    }

    private function askModel(array $chunks): array
    {
        $rows = [];
        $pending = array_keys($chunks);

        // 2 passes : les paquets échoués sont retentés une fois.
        for ($try = 0; $try < 2 && $pending; $try++) {
            $failed = [];

            foreach (array_chunk($pending, self::PARALLEL) as $batch) {
                $responses = Http::pool(function (Pool $pool) use ($batch, $chunks) {
                    foreach ($batch as $i) {
                        $pool->as((string) $i)
                            ->withToken(config('services.cloudflare.token'))
                            ->timeout(90)
                            ->post($this->base().'/ai/run/'.self::MODEL, [
                                'messages' => [
                                    ['role' => 'system', 'content' => self::PROMPT],
                                    ['role' => 'user', 'content' => $chunks[$i]],
                                ],
                                'response_format' => ['type' => 'json_object'],
                                'max_tokens' => 4096,
                                'temperature' => 0,
                                'seed' => 1,
                            ]);
                    }
                });

                foreach ($batch as $i) {
                    $response = $responses[(string) $i] ?? null;

                    if (! $response instanceof \Illuminate\Http\Client\Response || ! $response->successful()) {
                        $failed[] = $i;
                        continue;
                    }

                    foreach ($this->parse($response->json('result.response'), $chunks[$i]) as $item) {
                        $rows[] = $item;
                    }
                }
            }

            $pending = $failed;
        }

        // Jamais de résultat partiel silencieux.
        if ($pending) {
            throw new RuntimeException('Lecture incomplète (service IA surchargé). Réessayez dans un instant.');
        }

        $unique = [];
        foreach ($rows as $row) {
            $unique[mb_strtolower($row['title']).'|'.$row['price']] ??= $row;
        }

        return array_values($unique);
    }

    private function parse(mixed $raw, string $source): array
    {
        if (is_string($raw)) {
            $raw = preg_replace('/```(?:json)?/i', '', $raw);
            $decoded = json_decode(trim($raw), true);
            $raw = $decoded ?? (preg_match('/\[.*\]/s', $raw, $m) ? json_decode($m[0], true) : null);
        }

        if (is_array($raw) && isset($raw['items'])) {
            $raw = $raw['items'];
        }

        if (! is_array($raw)) {
            return [];
        }

        $items = [];
        foreach ($raw as $item) {
            if (! is_array($item) || trim((string) ($item['title'] ?? '')) === '') {
                continue;
            }

            // Anti-invention : on garde seulement les URLs réellement présentes dans le texte source.
            $images = array_filter((array) ($item['images'] ?? []), fn ($u) => is_string($u) && $u !== '' && str_contains($source, $u));

            $items[] = [
                'title' => trim((string) $item['title']),
                'price' => isset($item['price']) && is_numeric($item['price']) ? (string) $item['price'] : '',
                'description' => trim((string) ($item['description'] ?? '')),
                'type' => ($item['type'] ?? '') === 'service' ? 'service' : 'produit',
                'keywords' => trim((string) ($item['keywords'] ?? '')),
                'images' => implode(' | ', $images),
            ];
        }

        return $items;
    }

    private function base(): string
    {
        return 'https://api.cloudflare.com/client/v4/accounts/'.config('services.cloudflare.account_id');
    }

    private function utf8(string $text): string
    {
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);

        return mb_check_encoding($text, 'UTF-8') ? $text : mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
    }
}