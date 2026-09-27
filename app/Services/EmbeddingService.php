<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Vecteur texte (768 dim, BGE) qui capte aussi le sens de l'image :
 * l'image est d'abord décrite en texte (modèle vision), puis tout est
 * embeddé ensemble. Un seul espace vectoriel, fiable, pas cher.
 */
class EmbeddingService
{
    protected function endpoint(string $model): string
    {
        $accountId = config('services.cloudflare.account_id');
        return "https://api.cloudflare.com/client/v4/accounts/{$accountId}/ai/run/{$model}";
    }

    public function embed(string $text, ?string $imageUrl = null): ?array
    {
        $caption = $imageUrl ? $this->describeImage($imageUrl) : null;

        $fullText = trim($text.' '.$caption);

        return $this->embedText($fullText);
    }

    protected function embedText(string $text): ?array
    {
        try {
            $response = Http::withToken(config('services.cloudflare.token'))
                ->timeout(20)
                ->post($this->endpoint('@cf/baai/bge-base-en-v1.5'), [
                    'text' => $text,
                ]);

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