<?php

namespace App\Livewire;

use App\Services\EmbeddingService;
use App\Models\Company;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.public')]
class Search extends Component
{
    use WithPagination;

    #[Url]
    public string $mode = 'keyword'; // keyword | nearby | discover

    #[Url]
    public string $q = '';

    #[Url]
    public string $type = ''; // '' | produit | service | commerce

    #[Url]
    public ?float $maxPrice = null;

    #[Url]
    public ?float $maxDistance = null;

    #[Url(as: 'lat')]
    public ?float $userLat = null;

    #[Url(as: 'lng')]
    public ?float $userLng = null;

    protected $paginationTheme = 'tailwind';

    public function updated($property): void
    {
        if (in_array($property, ['mode', 'q', 'type', 'maxPrice', 'maxDistance'])) {
            $this->resetPage();
        }
    }

    public function setUserLocation(float $lat, float $lng): void
    {
        $this->userLat = $lat;
        $this->userLng = $lng;
    }

    public function setMode(string $mode): void
    {
        $this->mode = $mode;
        $this->resetPage();
    }

    /**
     * Les propriétés #[Url] viennent de l'adresse du navigateur : on les remet dans des valeurs sûres.
     */
    protected function sanitizeInputs(): void
    {
        if (! in_array($this->mode, ['keyword', 'nearby', 'discover'], true)) {
            $this->mode = 'keyword';
        }

        if (! in_array($this->type, ['', 'produit', 'service', 'commerce'], true)) {
            $this->type = '';
        }

        $this->q = mb_substr(trim($this->q), 0, 100);

        $this->maxPrice = $this->maxPrice !== null ? max(1, min($this->maxPrice, 1000000)) : null;
        $this->maxDistance = $this->maxDistance !== null ? max(1, min($this->maxDistance, 500)) : null;

        // Coordonnées hors plage ou incomplètes : on les ignore.
        if ($this->userLat === null || $this->userLng === null
            || abs($this->userLat) > 90 || abs($this->userLng) > 180) {
            $this->userLat = null;
            $this->userLng = null;
        }
    }

    public function render()
    {
        $this->sanitizeInputs();
        // "Commerces" cherche uniquement des entreprises.
        if ($this->type === 'commerce') {
            return view('livewire.search', [
                'products' => null,
                'companies' => $this->searchCompanies(),
            ]);
        }

        // "Tout" (type vide) : produits/services ET commerces, affichés
        // dans deux sections distinctes. Les autres types (produit/service)
        // ne cherchent que des produits.
        return view('livewire.search', [
            'products' => $this->searchProducts(),
            'companies' => $this->type === '' ? $this->searchCompanies() : null,
        ]);
    }

    protected ?array $cachedVector = null;
    protected bool $vectorComputed = false;

    /**
     * Calcule le vecteur de la requête une seule fois par affichage (évite 2 appels API).
     */
    protected function embedQueryOnce(): ?array
    {
        if (!$this->vectorComputed) {
            $this->vectorComputed = true;

            $this->cachedVector = ($this->mode === 'keyword' && !blank($this->q))
                ? app(EmbeddingService::class)->embedQuery($this->q)
                : null;
        }

        return $this->cachedVector;
    }

    /**
     * Ids candidats par proximité de sens (top 30), vides si pas en mode mot-clé.
     */
    protected function semanticIds(string $modelClass): array
    {
        $vector = $this->embedQueryOnce();

        if (!$vector) {
            return [];
        }

        return $modelClass::query()->semantic($vector)
            ->orderBy('semantic_distance')
            ->limit(30)
            ->pluck('id')
            ->all();
    }

    protected function applyModeScopes($query, string $table, array $semanticIds = [])
    {
        switch ($this->mode) {
            case 'nearby':
                if ($this->userLat && $this->userLng) {
                    $query->nearby($this->userLat, $this->userLng, 10);
                    $query->orderBy('distance');
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'discover':
                if ($this->userLat && $this->userLng) {
                    $query->nearby($this->userLat, $this->userLng, $this->maxDistance ?? 10);
                    $query->orderBy('distance');
                } else {
                    $query->whereNotNull('latitude')->latest();
                }
                break;

            case 'keyword':
            default:
                $query->where(function ($q) use ($semanticIds) {
                    $q->search($this->q ?: null);

                    if (!empty($semanticIds)) {
                        $q->orWhereIn($q->getModel()->getTable().'.id', $semanticIds);
                    }
                });

                if ($this->userLat && $this->userLng) {
                    $query->nearby($this->userLat, $this->userLng, $this->maxDistance ?? 200);
                    $query->orderBy('distance');
                } else {
                    $query->latest();
                }
                break;
        }

        return $query;
    }

    protected function searchProducts()
    {
        $query = Product::query()->ofType($this->type ?: null)->maxPrice($this->maxPrice);

        return $this->applyModeScopes($query, 'products', $this->semanticIds(Product::class))
            ->with(['images', 'company', 'reviews'])
            ->paginate(10, ['*'], 'productsPage');
    }

    protected function searchCompanies()
    {
        return $this->applyModeScopes(Company::query(), 'companies', $this->semanticIds(Company::class))
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->paginate(10, ['*'], 'companiesPage');
    }
}