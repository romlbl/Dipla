<?php

namespace App\Livewire\Company\Auth;

use App\Concerns\ThrottlesActions;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class ForgotPassword extends Component
{
    use ThrottlesActions;

    public string $email = '';
    public bool $sent = false;

    public function send(): void
    {
        $this->validate(['email' => ['required', 'email', 'max:255']]);
        $this->throttle('company-forgot', 5, 600, 'email');

        Password::broker('companies')->sendResetLink(['email' => $this->email]);

        // Message neutre : ne révèle pas si l'email existe.
        $this->sent = true;
    }
}