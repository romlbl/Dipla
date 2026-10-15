<?php

namespace App\Livewire\Company;

use App\Services\CatalogReader;
use App\Services\CatalogValidator;
use App\Services\ImageProcessor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\ProductImage;

class CatalogImport extends Component
{
    use WithFileUploads;
    private const BATCH = 4;
    public $file = null;
    public string $step = 'upload'; // upload | preview | done | error
    public ?string $message = null;

    public ?string $importId = null;
    public int $validCount = 0;
    public array $rejected = [];
    public int $rejectedCount = 0;

    // 3 prévisualisations : [index => produit]
    public array $previews = [];
    public array $status = [];      // [index => pending | added | skipped | error]
    public array $itemErrors = [];  // [index => message]
    public int $addedCount = 0;

    public int $cursor = 0;
    public int $failedCount = 0;
    public array $failures = [];

    public function updatedFile(): void
    {
        $this->resetErrorBag();
        $this->validate(['file' => ['required', 'file', 'max:10240']], [], ['file' => 'fichier']);
    }

    public function analyze(CatalogReader $reader, CatalogValidator $validator): void
    {
        $this->validate(['file' => ['required', 'file', 'max:10240']], [], ['file' => 'fichier']);

        set_time_limit(120);

        try {
            $data = $reader->read($this->file->getRealPath(), $this->file->getClientOriginalExtension());
        } catch (\RuntimeException $e) {
            $this->fail($e->getMessage());
            return;
        } catch (\Throwable $e) {
            report($e);
            $this->fail('Erreur inattendue pendant la lecture du fichier.');
            return;
        }

        $result = $validator->check($data['rows']);

        if ($result['valid'] === []) {
            $reasons = collect($result['rejected'])->pluck('reason')->countBy()->map(fn ($n, $r) => "$n × $r")->implode(', ');
            $this->fail('Aucun produit importable. Raisons : '.($reasons ?: 'fichier sans produit').'.');
            return;
        }

        // Produits valides gardés sur disque (pas dans le state Livewire : trop lourd).
        $this->importId = (string) Str::uuid();
        Storage::put($this->path(), json_encode($result['valid'], JSON_UNESCAPED_UNICODE));

        $this->validCount = count($result['valid']);
        $this->rejectedCount = count($result['rejected']);
        $this->rejected = array_slice($result['rejected'], 0, 30);

        $this->previews = collect($result['valid'])->shuffle()->take(3)->values()->all();
        $this->status = array_fill(0, count($this->previews), 'pending');
        $this->step = 'preview';
    }

    /**
     * Crée les produits d'un paquet. Images de tout le paquet envoyées en parallèle.
     * Retourne [index => message d'erreur] pour les produits en échec.
     */
    protected function storeMany(array $items, ImageProcessor $images): array
    {
        $company = auth('company')->user();
        $stored = $images->processMany(collect($items)->pluck('images')->flatten()->all());
        $errors = [];

        foreach ($items as $i => $item) {
            $urls = collect($item['images'])->map(fn ($u) => $stored[$u] ?? null)->filter()->values()->all();

            if ($urls === []) {
                $errors[$i] = 'Aucune image téléchargeable : produit non ajouté.';
                continue;
            }

            try {
                DB::transaction(function () use ($company, $item, $urls) {
                    $product = $company->products()->create([
                        'title' => $item['title'],
                        'price' => $item['price'],
                        'description' => $item['description'],
                        'type' => $item['type'],
                        'keywords' => $item['keywords'] ?: null,
                        'address' => $company->address,
                        'latitude' => $company->latitude,
                        'longitude' => $company->longitude,
                    ]);

                    // 1 seule requête, sans événements : pas de job embedding par image
                    // (le produit en lance déjà un, avec 15 s de délai).
                    ProductImage::insert(collect($urls)->map(fn ($url, $position) => [
                        'product_id' => $product->id,
                        'url' => $url,
                        'position' => $position,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])->all());
                });
            } catch (\Throwable $e) {
                report($e);
                $errors[$i] = "Erreur pendant l'ajout.";
            }
        }

        return $errors;
    }

    /** Valide les INFOS d'une prévisualisation. Rien n'est écrit en DB. */
    public function confirm(int $index): void
    {
        if (($this->status[$index] ?? null) === 'pending') {
            $this->status[$index] = 'validated';
        }
    }

    /** Lance l'import de TOUS les produits (les 3 exemples inclus). */
    public function addAll(): void
    {
        if ($this->step !== 'preview' || in_array('pending', $this->status, true)) {
            return;
        }

        $this->cursor = 0;
        $this->addedCount = 0;
        $this->failedCount = 0;
        $this->failures = [];
        $this->step = 'importing';
    }

    /** Ajoute un paquet. Retourne true s'il en reste (le navigateur rappelle). */
    public function processBatch(ImageProcessor $images): bool
    {
        if ($this->step !== 'importing') {
            return false;
        }

        set_time_limit(90);

        $items = json_decode((string) Storage::get($this->path()), true) ?? [];
        $batch = array_slice($items, $this->cursor, self::BATCH);

        $errors = $this->storeMany($batch, $images);

        foreach ($batch as $i => $item) {
            if (isset($errors[$i])) {
                $this->failedCount++;
                if (count($this->failures) < 10) {
                    $this->failures[] = $item['title'];
                }
            } else {
                $this->addedCount++;
            }
        }

        $this->cursor += count($batch);

        if ($batch === [] || $this->cursor >= $this->validCount) {
            $this->finishImport();
            return false;
        }

        return true;
    }

    public function failImport(): void
    {
        $this->fail('Connexion perdue pendant l\'import. '.$this->addedCount.' produit(s) déjà ajouté(s).');
    }

    protected function finishImport(): void
    {
        $this->message = $this->addedCount.' produit(s)/service(s) ajouté(s).';

        if ($this->failedCount > 0) {
            $this->message .= ' '.$this->failedCount.' non ajouté(s) (image introuvable ou erreur) : '
                .implode(', ', $this->failures).($this->failedCount > count($this->failures) ? '…' : '');
        }

        $this->step = $this->addedCount > 0 ? 'done' : 'error';
    }

    public function skip(int $index): void
    {
        if (($this->status[$index] ?? null) === 'pending') {
            $this->status[$index] = 'skipped';
        }
    }

    public function resetImport(): void
    {
        if ($this->importId) {
            Storage::delete($this->path());
        }

        $this->reset([
            'file', 'step', 'message', 'importId', 'validCount', 'rejected', 'rejectedCount',
            'previews', 'status', 'itemErrors', 'addedCount', 'cursor', 'failedCount', 'failures',
        ]);
        $this->resetErrorBag();
    }

    protected function path(): string
    {
        return 'imports/'.auth('company')->id().'/'.$this->importId.'.json';
    }

    protected function fail(string $message): void
    {
        $this->message = $message;
        $this->step = 'error';
    }

    public function render()
    {
        return view('livewire.company.catalog-import');
    }
}