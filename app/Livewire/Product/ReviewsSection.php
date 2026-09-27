<?php

namespace App\Livewire\Product;

use App\Models\Product;
use App\Models\Review;
use Livewire\Component;

class ReviewsSection extends Component
{
    public Product $product;

    public string $subject = '';
    public string $content = '';
    public int $rating = 5;

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function submitReview(): void
    {
        if (!auth()->check()) {
            $this->addError('auth', 'Connectez-vous pour laisser un avis.');
            return;
        }

        $this->validate([
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:2000'],
            'rating' => ['required', 'integer', 'between:1,5'],
        ]);

        Review::create([
            'user_id' => auth()->id(),
            'product_id' => $this->product->id,
            'subject' => $this->subject,
            'content' => $this->content,
            'rating' => $this->rating,
        ]);

        $this->reset(['subject', 'content', 'rating']);
        $this->rating = 5;

        $this->product->refresh();
    }

    public function render()
    {
        $reviews = $this->product->reviews()->with('user')->latest()->get();

        return view('livewire.product.reviews-section', compact('reviews'));
    }
}