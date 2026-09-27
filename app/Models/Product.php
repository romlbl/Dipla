<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'title',
        'price',
        'description',
        'type',
        'keywords',
        'address',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(Discussion::class)->whereNull('parent_id');
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function viewHistory(): HasMany
    {
        return $this->hasMany(ViewHistory::class);
    }

    public function averageRating(): float
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

    protected static function booted(): void
    {
        static::deleting(function (Product $product) {
            $imageKit = app(\App\Services\ImageKitService::class);

            foreach ($product->images as $image) {
                $imageKit->delete($image->url);
            }

            $product->images()->delete();
        });

        static::saved(function (Product $product) {
            if ($product->wasRecentlyCreated || $product->wasChanged(['title', 'description', 'keywords'])) {
                \App\Jobs\UpdateProductEmbedding::dispatch($product->id);
            }
        });
    }
    /**
     * Recherche plein texte optimisée (FTS + Préfixes + Fautes de frappe).
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = trim($term);

        // Prépare la requête de préfixe pour autocomplétion (ex: "chauss" -> "chauss:*")
        $words = array_filter(explode(' ', $term));
        $prefixQuery = implode(' & ', array_map(function ($word) {
            $cleanWord = preg_replace('/[^\w]/u', '', $word);
            return $cleanWord ? $cleanWord . ':*' : '';
        }, $words));

        return $query->where(function ($q) use ($term, $prefixQuery) {
            // 1. Syntaxe moderne Postgres (supporte les guillemets, le OR, etc.)
            $q->whereRaw("search_vector @@ websearch_to_tsquery('french', ?)", [$term]);

            // 2. Recherche par préfixe (trouve les mots incomplets)
            if (!empty($prefixQuery)) {
                $q->orWhereRaw("search_vector @@ to_tsquery('french', ?)", [$prefixQuery]);
            }

            // 3. Tolérance aux fautes de frappe sur le titre
            $q->orWhereRaw("similarity(unaccent(title), unaccent(?)) > 0.25", [$term]);
        });
    }

    /**
     * Filtre par distance (km) depuis un point donné, formule de Haversine.
     * Ajoute aussi une colonne calculée "distance" utilisable pour trier.
     */
    public function scopeNearby(Builder $query, float $lat, float $lng, ?float $radiusKm = null): Builder
    {
        $haversine = "(
            6371 * acos(
                LEAST(1, GREATEST(-1,
                    cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?))
                    + sin(radians(?)) * sin(radians(latitude))
                ))
            )
        )";

        $query
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->selectRaw("products.*, {$haversine} AS distance", [$lat, $lng, $lat]);

        if ($radiusKm !== null) {
            // WHERE (pas HAVING) : c'est un filtre ligne par ligne, pas un filtre post-agrégation.
            $query->whereRaw("{$haversine} <= ?", [$lat, $lng, $lat, $radiusKm]);
        }

        return $query;
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        if (blank($type)) {
            return $query;
        }

        return $query->where('type', $type);
    }

    public function scopeMaxPrice(Builder $query, ?float $price): Builder
    {
        if (blank($price)) {
            return $query;
        }

        return $query->where('price', '<=', $price);
    }

    public function priceLabel(): string
    {
        return $this->price !== null
            ? number_format($this->price, 2) . ' €'
            : 'Variable';
    }

    /**
     * Filtre/trie par proximité vectorielle (sens du texte + de l'image).
     * $vector = null → ne touche pas à la requête.
     */
    public function scopeSemantic(Builder $query, ?array $vector, float $maxDistance = 0.3): Builder
    {
        if (!$vector) {
            return $query;
        }

        $literal = '['.implode(',', $vector).']';

        return $query
            ->whereNotNull('embedding')
            ->selectRaw('products.*, (embedding <=> ?) AS semantic_distance', [$literal])
            ->whereRaw('(embedding <=> ?) <= ?', [$literal, $maxDistance]) // Filtre les produits trop éloignés
            ->orderByRaw('embedding <=> ?', [$literal]);
    }

}