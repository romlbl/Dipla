<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Calcule un vecteur CLIP (512 dim) à partir de texte et/ou image.
 * Texte et image partagent le même espace vectoriel : on peut les moyenner.
 */
class EmbeddingService
{
    protected const MODEL_URL = 'https://api-inference.huggingface.co/models/sentence-transformers/clip-ViT-B-32';

    /**
     * Vecteur combiné texte + image (ou texte seul si pas de photo).
     */
    public function embed(string $text, ?string $imageUrl = null): ?array
    {
        $textVec = $this->embedText($text);

        if (!$textVec) {
            return null;
        }

        if (!$imageUrl) {
            return $textVec;
        }

        $imageVec = $this->embedImage($imageUrl);

        if (!$imageVec) {
            return $textVec;
        }

        // Moyenne simple des deux vecteurs (même espace CLIP).
        return array_map(fn ($t, $i) => ($t + $i) / 2, $textVec, $imageVec);
    }

    protected function embedText(string $text): ?array
    {
        try {
            $response = Http::withToken(config('services.huggingface.key'))
                ->timeout(30)
                ->post(self::MODEL_URL, [
                    'inputs' => $text,
                ]);

            if (!$response->successful()) {
                Log::error('HuggingFace embedding texte échoué', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $data = $response->json();

            Log::info('Réponse embedding texte', [
                'type' => gettype($data),
                'structure' => $data,
            ]);

            if (is_array($data) && isset($data[0]) && is_array($data[0])) {
                return $data[0];
            }

            return is_array($data) ? $data : null;

        } catch (\Throwable $e) {
            Log::error('Exception embedding texte', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function embedImage(string $imageUrl): ?array
    {
        try {
            $imageBytes = Http::timeout(10)->get($imageUrl)->body();

            $response = Http::withToken(config('services.huggingface.key'))
                ->timeout(15)
                ->withBody($imageBytes, 'application/octet-stream')
                ->post(self::MODEL_URL);

            if ($response->successful()) {
                $data = $response->json();
                return is_array($data[0] ?? null) ? $data[0] : $data;
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('Embedding image échoué', ['message' => $e->getMessage()]);
            return null;
        }
    }
}