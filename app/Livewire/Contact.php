<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.guest')]
class Contact extends Component
{
    public const SUBJECTS = [
        'question' => 'Question',
        'bug' => 'Problème technique',
        'content' => 'Signaler un contenu',
        'upgrade' => 'Proposer une amélioration',
        'data' => 'Mes données personnelles',
        'other' => 'Autre',
    ];

    public string $name = '';
    public string $email = '';

    #[Url(as: 'motif')]
    public string $subject = 'question';

    public string $message = '';

    public string $website = ''; // piège anti-robot : doit rester vide

    public bool $sent = false;

    public function mount(): void
    {
        // Pré-remplit avec le compte connecté (utilisateur ou commerce).
        $account = auth()->user() ?? auth('company')->user();

        if ($account) {
            $this->name = $account->name;
            $this->email = $account->email;
        }
    }

    public function send(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', Rule::in(array_keys(self::SUBJECTS))],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        // Champ caché rempli = robot : on fait semblant d'avoir envoyé.
        if ($this->website !== '') {
            $this->sent = true;
            return;
        }

        // 3 messages max par 10 minutes et par IP.
        if (! RateLimiter::attempt('contact:'.request()->ip(), 3, fn () => true, 600)) {
            $this->addError('message', 'Trop de messages envoyés. Réessaie dans quelques minutes.');
            return;
        }

        $name = preg_replace('/\s+/', ' ', $data['name']);

        try {
            Mail::raw(
                "De : {$name} <{$data['email']}>\n\n{$data['message']}",
                fn ($mail) => $mail
                    ->to(config('services.contact.to'))
                    ->replyTo($data['email'], $name)
                    ->subject('[Dipla] '.self::SUBJECTS[$data['subject']])
            );
        } catch (\Throwable $e) {
            report($e);
            $this->addError('message', "Envoi impossible pour le moment. Réessaie plus tard.");
            return;
        }

        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.contact');
    }
}