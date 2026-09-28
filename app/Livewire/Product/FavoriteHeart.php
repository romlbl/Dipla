<?php

namespace App\Livewire\Product;

use App\Models\Product;
use Livewire\Component;

class FavoriteHeart extends Component
{
    public Product $product;
    public bool $isFavorited = false;

    /** Ids favoris de l'utilisateur, chargés une seule fois par requête. */
    protected static ?array $favoriteIds = null;

    protected static function favoriteIds(): array
    {
        return static::$favoriteIds ??= auth()->user()->favorites()->pluck('products.id')->all();
    }

    public function mount(Product $product): void
    {
        $this->product = $product;
        $this->isFavorited = auth()->check() && in_array($product->id, static::favoriteIds(), true);
    }

    public function toggle(): void
    {
        if (!auth()->check()) {
            return;
        }

        if ($this->isFavorited) {
            $this->product->favoritedBy()->detach(auth()->id());
        } else {
            $this->product->favoritedBy()->attach(auth()->id());
        }
        static::$favoriteIds = null;
        $this->isFavorited = !$this->isFavorited;
    }

    public function render()
    {
        return view('livewire.product.favorite-heart');
    }
}