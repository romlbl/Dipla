<?php

namespace App\Livewire\Company\Auth;

use App\Models\Company;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class Register extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $address = '';
    public ?float $latitude = null;
    public ?float $longitude = null;
    public bool $acceptTerms = false;

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:companies,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'address' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'acceptTerms' => ['accepted'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        unset($validated['password_confirmation'], $validated['acceptTerms']);

        $company = Company::create($validated);

        event(new Registered($company));

        auth('company')->login($company);

        $this->redirect(route('company.dashboard'), navigate: true);
    }
}