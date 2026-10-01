<?php

namespace App\Livewire\Company;

use App\Models\Product;
use App\Services\ImageKitService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductImageManager extends Component
{
    use WithFileUploads;

    public Product $product;

    public $newImages = [];

    public function mount(Product $product): void
    {
        Gate::forUser(auth('company')->user())->authorize('update', $product);

        $this->product = $product;
    }

    public function updatedNewImages(): void
    {
        $this->validate([
            'newImages' => ['array', 'max:4'],
            'newImages.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($this->product->images()->count() + count($this->newImages) > 4) {
            $this->addError('newImages', 'Maximum 4 photos par produit.');
            $this->newImages = [];
            return;
        }

        $imageKit = app(ImageKitService::class);
        $position = ($this->product->images()->max('position') ?? -1) + 1;

        foreach ($this->newImages as $file) {
            $url = $imageKit->upload($file->getRealPath());

            $this->product->images()->create([
                'url' => $url,
                'position' => $position++,
            ]);
        }

        $this->newImages = [];
        $this->product->refresh();
    }

    public function deleteImage(int $imageId): void
    {
        $image = $this->product->images()->findOrFail($imageId);

        app(ImageKitService::class)->delete($image->url);

        $image->delete();
        $this->product->refresh();
    }

    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            $this->product->images()->where('id', $id)->update(['position' => $index]);
        }

        $this->product->refresh();
    }

    public function render()
    {
        return view('livewire.company.product-image-manager', [
            'images' => $this->product->images()->orderBy('position')->get(),
        ]);
    }
}