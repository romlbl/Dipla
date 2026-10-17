<?php

namespace App\Livewire\Company;

use Illuminate\Support\Facades\DB;
use App\Models\Company;
use App\Services\ImageKitService;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditStorefront extends Component
{
    use WithFileUploads;

    public Company $company;

    public string $name = '';
    public string $phone = '';
    public string $website = '';
    public string $address = '';
    public ?float $latitude = null;
    public ?float $longitude = null;
    public string $description = '';
    public bool $updateProductsAddress = true;

    public $newCoverImage = null;
    public $newCardImage = null;
    public $newAvatarImage = null;

    public array $openingHours = [];

    protected array $days = [
        'mon' => 'Lundi',
        'tue' => 'Mardi',
        'wed' => 'Mercredi',
        'thu' => 'Jeudi',
        'fri' => 'Vendredi',
        'sat' => 'Samedi',
        'sun' => 'Dimanche',
    ];

    public function mount(Company $company): void
    {
        $this->company = $company;
        $this->name = $company->name;
        $this->phone = $company->phone ?? '';
        $this->website = $company->website ?? '';
        $this->address = $company->address;
        $this->latitude = $company->latitude ? (float) $company->latitude : null;
        $this->longitude = $company->longitude ? (float) $company->longitude : null;
        $this->description = $company->description ?? '';

        $existing = $company->opening_hours ?? [];

        foreach ($this->days as $key => $label) {
            $this->openingHours[$key] = array_merge([
                'closed' => false,
                'open' => '09:00',
                'close' => '18:00',
                'has_break' => false,
                'break_start' => '12:00',
                'break_end' => '14:00',
            ], $existing[$key] ?? []);
        }
    }

    public function getDaysProperty(): array
    {
        return $this->days;
    }

    public function save(): void
    {
        $this->website = trim($this->website);
        if ($this->website !== '' && ! preg_match('#^https?://#i', $this->website)) {
            $this->website = 'https://'.$this->website;
        }
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'newCoverImage' => ['nullable', 'image', 'max:5120'],
            'newCardImage' => ['nullable', 'image', 'max:5120'],
            'newAvatarImage' => ['nullable', 'image', 'max:5120'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9 +().\-]{6,30}$/'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'openingHours' => ['required', 'array'],
            'openingHours.*.closed' => ['boolean'],
            'openingHours.*.open' => ['nullable', 'date_format:H:i'],
            'openingHours.*.close' => ['nullable', 'date_format:H:i'],
            'openingHours.*.has_break' => ['boolean'],
            'openingHours.*.break_start' => ['nullable', 'date_format:H:i'],
            'openingHours.*.break_end' => ['nullable', 'date_format:H:i'],
        ]);
        $hours = $this->sanitizedOpeningHours();

        if (! $this->hoursAreCoherent($hours)) {
            return;
        }

        $data = [
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?: null,
            'website' => $validated['website'] ?: null,
            'address' => $validated['address'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'description' => $validated['description'],
            'opening_hours' => $hours,
        ];

        $imageKit = app(ImageKitService::class);

        $data = array_merge($data, $this->uploadIfPresent($imageKit, $this->newCoverImage, $this->company->cover_image_url, 'dipla/companies', 'cover_image_url'));
        $data = array_merge($data, $this->uploadIfPresent($imageKit, $this->newCardImage, $this->company->card_image_url, 'dipla/companies/cards', 'card_image_url'));
        $data = array_merge($data, $this->uploadIfPresent($imageKit, $this->newAvatarImage, $this->company->avatar_image_url, 'dipla/companies/avatars', 'avatar_image_url'));

        // À calculer AVANT la mise à jour : on compare avec l'adresse encore enregistrée.
        $addressChanged = $validated['address'] !== $this->company->address
            || round((float) $validated['latitude'], 6) !== round((float) $this->company->latitude, 6)
            || round((float) $validated['longitude'], 6) !== round((float) $this->company->longitude, 6);

        DB::transaction(function () use ($data, $addressChanged) {
            $this->company->update($data);

            if ($this->updateProductsAddress && $addressChanged) {
                $this->company->products()->update([
                    'address' => $data['address'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                ]);
            }
        });

        $this->dispatch('storefront-saved');
    }

    /**
     * Ne conserve que les 7 jours connus avec leurs champs, quoi qu'envoie le navigateur.
     */
    protected function sanitizedOpeningHours(): array
    {
        $hours = [];

        foreach (array_keys($this->days) as $key) {
            $day = $this->openingHours[$key] ?? [];

            $hours[$key] = [
                'closed' => (bool) ($day['closed'] ?? false),
                'open' => ($day['open'] ?? null) ?: '09:00',
                'close' => ($day['close'] ?? null) ?: '18:00',
                'has_break' => (bool) ($day['has_break'] ?? false),
                'break_start' => ($day['break_start'] ?? null) ?: '12:00',
                'break_end' => ($day['break_end'] ?? null) ?: '14:00',
            ];
        }

        return $hours;
    }

    /**
     * Vérifie l'ordre : ouverture < début pause < fin pause < fermeture.
     */
    protected function hoursAreCoherent(array $hours): bool
    {
        $valid = true;

        foreach ($this->days as $key => $label) {
            $day = $hours[$key];

            if ($day['closed']) {
                continue;
            }

            if ($day['open'] >= $day['close']) {
                $this->addError("openingHours.$key.close", "$label : la fermeture doit être après l'ouverture.");
                $valid = false;
                continue;
            }

            if ($day['has_break'] && ! ($day['open'] < $day['break_start']
                && $day['break_start'] < $day['break_end']
                && $day['break_end'] < $day['close'])) {
                $this->addError("openingHours.$key.close", "$label : la pause doit être comprise entre l'ouverture et la fermeture.");
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Remplace une photo de devanture : supprime l'ancienne sur ImageKit si besoin,
     * envoie la nouvelle, et retourne le tableau à fusionner dans les données à sauvegarder.
     */
    protected function uploadIfPresent(ImageKitService $imageKit, $newFile, ?string $existingUrl, string $folder, string $column): array
    {
        if (!$newFile) {
            return [];
        }

        if ($existingUrl) {
            $imageKit->delete($existingUrl);
        }

        return [$column => $imageKit->upload($newFile->getRealPath(), $folder)];
    }

    public function render()
    {
        return view('livewire.company.edit-storefront');
    }
}