<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Table lue en PHP -> l'IA repère seulement QUELLE colonne = QUEL champ (1 appel, 5 lignes).
 * Les lignes sont ensuite converties en PHP : exact, identique à chaque fois.
 */
class CatalogMapper
{
    private const MODEL = '@cf/meta/llama-3.3-70b-instruct-fp8-fast';
    private const FIELDS = ['title', 'price', 'description', 'type', 'keywords', 'images'];

    private const PROMPT = <<<'TXT'
        You map catalog columns (any language, any naming) to product fields. Return ONLY a JSON object:
        {"title": "column or null", "price": ["columns holding the selling price"], "description": "column or null", "type": "column or null", "keywords": "column or null", "images": ["every column holding image URLs or a photo URL"]}
        Rules:
        - Use column names exactly as given. Never invent one.
        - "title" = commercial name of the product/service (not an internal reference or SKU).
        - "price" = selling price to the customer, best first: prefer tax-included (TTC, incl. VAT, retail, suggested) over tax-excluded (HT). Never stock, weight, VAT rate or cost.
        - "description" = the longest descriptive text. 
        - "keywords" = tags/categories.
        - Ignore noise columns (stock, internal codes, logistics, mood...).
        TXT;

    public function map(array $data): array
    {
        $mapping = $this->mapping($data['headers'], array_slice($data['rows'], 0, 5));
        $rows = [];

        foreach ($data['rows'] as $row) {
            $get = fn (?string $col) => $col ? trim((string) ($row[$col] ?? '')) : '';
            $images = collect($mapping['images'])->map(fn ($c) => $get($c))->filter()->implode(' | ');
            $title = $get($mapping['title']);

            // Ligne sans titre mais avec image (ex: Shopify) = image du produit précédent.
            if ($title === '') {
                if ($images !== '' && $rows !== []) {
                    $rows[array_key_last($rows)]['images'] .= ' | '.$images;
                }
                continue;
            }

            $rows[] = [
                'title' => $title,
                'price' => $this->firstPrice($mapping['price'], $get),
                'description' => $get($mapping['description']),
                'type' => str_contains(mb_strtolower($get($mapping['type'])), 'serv') ? 'service' : 'produit',
                'keywords' => $get($mapping['keywords']),
                'images' => $images,
            ];
        }

        return ['headers' => self::FIELDS, 'rows' => $rows];
    }

    /** 1re colonne de prix contenant un nombre. "à partir de 149,90 €" -> "149.90". */
    private function firstPrice(array $columns, \Closure $get): string
    {
        foreach ($columns as $column) {
            $text = str_replace(',', '.', $get($column));

            if (preg_match('/\d+(?:\.\d+)?/', str_replace(' ', '', $text), $m)) {
                return $m[0];
            }
        }

        return '';
    }

    private function mapping(array $headers, array $sample): array
    {
        return Cache::remember('catalog-map:'.md5(json_encode($headers)), now()->addDays(30), function () use ($headers, $sample) {
            try {
                $response = Http::withToken(config('services.cloudflare.token'))
                    ->timeout(40)
                    ->post('https://api.cloudflare.com/client/v4/accounts/'.config('services.cloudflare.account_id').'/ai/run/'.self::MODEL, [
                        'messages' => [
                            ['role' => 'system', 'content' => self::PROMPT],
                            ['role' => 'user', 'content' => json_encode(['columns' => $headers, 'sample_rows' => $sample], JSON_UNESCAPED_UNICODE)],
                        ],
                        'response_format' => ['type' => 'json_object'],
                        'max_tokens' => 400,
                        'temperature' => 0,
                        'seed' => 1,
                    ]);
            } catch (\Throwable) {
                throw new RuntimeException('Service IA indisponible. Réessayez dans un instant.');
            }

            $raw = $response->json('result.response');
            $raw = is_string($raw) ? json_decode($raw, true) : $raw;

            if (! $response->successful() || ! is_array($raw)) {
                throw new RuntimeException("L'IA n'a pas pu repérer les colonnes du fichier.");
            }

            $valid = fn ($c) => is_string($c) && in_array($c, $headers, true) ? $c : null;
            $mapping = [];
            foreach (['title', 'description', 'type', 'keywords'] as $field) {
                $mapping[$field] = $valid($raw[$field] ?? null);
            }
            $mapping['images'] = array_values(array_filter(array_map($valid, (array) ($raw['images'] ?? []))));
            $mapping['price'] = array_values(array_filter(array_map($valid, (array) ($raw['price'] ?? []))));
            if (! $mapping['title']) {
                throw new RuntimeException("Colonne du nom des produits introuvable dans le fichier.");
            }

            return $mapping;
        });
    }
}