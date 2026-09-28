<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Vecteur texte multilingue (BGE-M3, 1024 dim). L'image est d'abord décrite
 * en texte (modèle vision), puis tout est embeddé ensemble.
 */
class EmbeddingService
{
    protected const TEXT_MODEL = '@cf/baai/bge-m3';

    protected function endpoint(string $model): string
    {
        $accountId = config('services.cloudflare.account_id');
        return "https://api.cloudflare.com/client/v4/accounts/{$accountId}/ai/run/{$model}";
    }

    public function embed(string $text, ?string $imageUrl = null): ?array
    {
        $caption = $imageUrl ? $this->describeImage($imageUrl) : null;

        return $this->embedText(trim($text.' '.$caption));
    }

    /**
     * Vecteur d'une recherche, mis en cache 24 h : évite un appel API
     * à chaque rendu Livewire (pagination, filtres, sliders).
     */
    public function embedQuery(string $query): ?array
    {
        $query = mb_strtolower(trim($query));

        return Cache::remember(
            'embed:bge-m3:'.md5($query),
            now()->addDay(),
            fn () => $this->embed($query)
        );
    }

    protected function embedText(string $text): ?array
    {
        try {
            $response = Http::withToken(config('services.cloudflare.token'))
                ->timeout(20)
                ->post($this->endpoint(self::TEXT_MODEL), ['text' => $text]);

            if (!$response->successful()) {
                Log::error('Embedding texte Cloudflare échoué', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json('result.data.0');
        } catch (\Throwable $e) {
            Log::error('Exception embedding texte', ['message' => $e->getMessage()]);
            return null;
        }
    }

    protected function describeImage(string $imageUrl): ?string
    {
        try {
            $imageBytes = Http::timeout(10)->get($imageUrl)->body();
            $imageArray = array_values(unpack('C*', $imageBytes));

            $response = Http::withToken(config('services.cloudflare.token'))
                ->timeout(30)
                ->post($this->endpoint('@cf/llava-hf/llava-1.5-7b-hf'), [
                    'image' => $imageArray,
                    'prompt' => 'Describe this product photo in a few keywords.',
                    'max_tokens' => 50,
                ]);

            if (!$response->successful()) {
                Log::warning('Description image échouée', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            return $response->json('result.description');
        } catch (\Throwable $e) {
            Log::warning('Exception description image', ['message' => $e->getMessage()]);
            return null;
        }
    }
}