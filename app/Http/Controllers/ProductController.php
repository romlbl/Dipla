<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ViewHistory;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        $product->load(['images', 'company', 'reviews.user']);
        Product::whereKey($product->id)->increment('views_count');

        // Historique de consultation : seulement pour un utilisateur particulier connecté
        if (auth()->check()) {
            ViewHistory::record(auth()->user(), $product);
        }

    $keywords = collect(explode(',', $product->keywords ?? ''))
        ->map(fn ($k) => mb_strtolower(trim($k)))
        ->filter()
        ->unique()
        ->values();

    // Produits qui partagent au moins 2 mots-clés, calculé par Postgres.
    $related = $keywords->count() < 2
        ? collect()
        : Product::query()
            ->with('images')
            ->where('id', '!=', $product->id)
            ->whereRaw(
                "(SELECT count(*) FROM (
                    SELECT trim(k) FROM unnest(string_to_array(lower(products.keywords), ',')) AS k
                    INTERSECT
                    SELECT unnest(ARRAY[".$keywords->map(fn () => '?')->implode(',')."]::text[])
                ) AS shared) >= 2",
                $keywords->all()
            )
            ->limit(4)
            ->get();

        return view('products.show', compact('product', 'related'));
    }
}