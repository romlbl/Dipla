<?php

namespace App\Jobs;

use App\Models\Product;
use App\Services\EmbeddingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class UpdateProductEmbedding implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $productId) {}

    public function handle(EmbeddingService $embedding): void
    {
        $product = Product::with('images')->find($this->productId);

        if (!$product) {
            Log::warning('Produit introuvable pour embedding', [
                'product_id' => $this->productId,
            ]);

            return;
        }

        $text = trim(
            $product->title . ' ' .
            $product->description . ' ' .
            $product->keywords
        );

        $imageUrl = $product->images->first()?->url;

        Log::info('Génération embedding démarrée', [
            'product_id' => $product->id,
            'text' => $text,
            'image_url' => $imageUrl,
        ]);

        $vector = $embedding->embed($text, $imageUrl);

        if (!$vector) {
            Log::error('Embedding vide', [
                'product_id' => $product->id,
            ]);

            return;
        }

        Log::info('Embedding reçu', [
            'product_id' => $product->id,
            'type' => gettype($vector),
            'count' => is_array($vector) ? count($vector) : null,
            'first_values' => is_array($vector)
                ? array_slice($vector, 0, 5)
                : null,
        ]);

        DB::update(
            'UPDATE products SET embedding = ?::vector WHERE id = ?',
            [
                '[' . implode(',', $vector) . ']',
                $product->id,
            ]
        );

        Log::info('Embedding enregistré', [
            'product_id' => $product->id,
        ]);
    }
    public function uniqueId(): string
    {
        return (string) $this->productId;
    }
}