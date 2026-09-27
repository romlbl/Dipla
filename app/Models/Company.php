<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Company extends Authenticatable
{
   use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'address',
        'latitude',
        'longitude',
        'cover_image_url',
        'card_image_url',
        'avatar_image_url',
        'description',
        'opening_hours',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'opening_hours' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (Company $company) {
            if ($company->wasRecentlyCreated || $company->wasChanged(['name', 'description', 'cover_image_url', 'card_image_url'])) {
                \App\Jobs\UpdateCompanyEmbedding::dispatch($company->id);
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function pendingQuestionsCount(): int
    {
        return \App\Models\Discussion::whereIn('product_id', $this->products()->pluck('id'))
            ->whereNull('parent_id')
            ->doesntHave('replies')
            ->count();
    }

    public function isOpenNow(): ?bool
    {
        $hours = $this->opening_hours;

        if (!$hours) {
            return null;
        }

        $localNow = now('Europe/Paris');

        $dayKey = strtolower($localNow->format('D')); // mon, tue, wed...
        $today = $hours[$dayKey] ?? null;

        if (!$today || ($today['closed'] ?? true)) {
            return false;
        }

        $open = $today['open'] ?? null;
        $close = $today['close'] ?? null;

        if (!$open || !$close) {
            return null;
        }

        $currentTime = $localNow->format('H:i');

        if ($currentTime < $open || $currentTime > $close) {
            return false;
        }

        // Pause (ex : midi) : fermé entre début et fin de pause.
        $breakStart = $today['break_start'] ?? null;
        $breakEnd = $today['break_end'] ?? null;

        if (($today['has_break'] ?? false) && $breakStart && $breakEnd
            && $currentTime >= $breakStart && $currentTime < $breakEnd) {
            return false;
        }

        return true;
    }

    public function scopeSearch(\Illuminate\Database\Eloquent\Builder $query, ?string $term): \Illuminate\Database\Eloquent\Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function ($q) use ($term) {
            $q->whereRaw("unaccent(name) ILIKE unaccent(?)", ["%{$term}%"])
            ->orWhereRaw("unaccent(address) ILIKE unaccent(?)", ["%{$term}%"])
            ->orWhereRaw("word_similarity(unaccent(?), unaccent(name)) > 0.25", [$term]);
        });
    }

    public function scopeNearby(\Illuminate\Database\Eloquent\Builder $query, float $lat, float $lng, ?float $radiusKm = null): \Illuminate\Database\Eloquent\Builder
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
            ->selectRaw("companies.*, {$haversine} AS distance", [$lat, $lng, $lat]);

        if ($radiusKm !== null) {
            $query->whereRaw("{$haversine} <= ?", [$lat, $lng, $lat, $radiusKm]);
        }

        return $query;
    }

    public function scopeSemantic(\Illuminate\Database\Eloquent\Builder $query, ?array $vector): \Illuminate\Database\Eloquent\Builder
    {
        if (!$vector) {
            return $query;
        }

        $literal = '['.implode(',', $vector).']';

        return $query
            ->whereNotNull('embedding')
            ->selectRaw('companies.*, (embedding <=> ?) AS semantic_distance', [$literal]);
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(Review::class, Product::class);
    }

    public function averageRating(): float
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }


}