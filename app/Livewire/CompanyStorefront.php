<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Company;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.public')]
class CompanyStorefront extends Component
{
    use WithPagination;

    public Company $company;

    #[Url]
    public string $search = '';

    protected $paginationTheme = 'tailwind';

    public function mount(Company $company): void
    {
        $this->company = $company;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $semanticIds = [];

        if (!blank($this->search)) {
            $vector = app(\App\Services\EmbeddingService::class)->embed($this->search);

            if ($vector) {
                $semanticIds = Product::query()->semantic($vector)
                    ->orderBy('semantic_distance')
                    ->limit(30)
                    ->pluck('id')
                    ->all();
            }
        }

        $products = $this->company->products()
            ->when($this->search !== '', function ($q) use ($semanticIds) {
                $q->where(function ($inner) use ($semanticIds) {
                    $inner->search(mb_substr(trim($this->search), 0, 100));

                    if (!empty($semanticIds)) {
                        $inner->orWhereIn('id', $semanticIds);
                    }
                });
            })
            ->with(['images', 'company', 'reviews'])
            ->latest()
            ->paginate(8);

        return view('livewire.company-storefront', [
            'products' => $products,
        ]);
    }
}