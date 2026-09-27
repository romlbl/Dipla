<?php

namespace App\Livewire\Product;

use App\Models\Product;
use Livewire\Component;

class FavoriteButton extends Component
{
    public Product $product;
    public bool $isFavorited = false;

    public function mount(Product $product): void
    {
        $this->product = $product;

        if (auth()->check()) {
            $this->isFavorited = $product->favoritedBy()->where('user_id', auth()->id())->exists();
        }
    }

    public function toggle(): void
    {
        if (!auth()->check()) {
            $this->dispatch('notify', message: 'Connectez-vous pour ajouter aux favoris.');
            return;
        }

        if ($this->isFavorited) {
            $this->product->favoritedBy()->detach(auth()->id());
        } else {
            $this->product->favoritedBy()->attach(auth()->id());
        }

        $this->isFavorited = !$this->isFavorited;
    }

    public function render()
    {
        return view('livewire.product.favorite-button');
    }
}