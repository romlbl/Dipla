@push('body-top')
    <canvas id="neat-home-background" class="fixed inset-0 z-0 h-screen w-screen" style="pointer-events: none;" aria-hidden="true"></canvas>
@endpush

<div class="w-full z-10 max-w-xl mx-auto">

    <div class="mb-6 text-center">
        <a href="{{ route('home') }}" wire:navigate class="text-4xl font-extrabold tracking-tight text-[#1E293B] md:text-5xl">
            Dipla
        </a>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-[#1E293B] md:text-3xl">Nous contacter</h1>
        <p class="mt-1 text-sm text-[#333333]/70">Une question, un souci technique, un contenu à signaler ? Écris-nous.</p>
    </div>

    @if($sent)
        <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-700">
            Message envoyé, merci ! Nous vous répondons par e-mail.
        </div>
        <a href="{{ route('home') }}" wire:navigate
           class="mt-4 inline-flex text-sm font-medium text-[#1E3D59] hover:underline">&larr; Retour à l'accueil</a>
    @else
        <form wire:submit="send" class="relative flex flex-col gap-4 rounded-2xl border border-[#E2E8F0] bg-[#FAFAFF] p-5 shadow-sm sm:p-6">

            <div>
                <label for="contact-name" class="mb-1 block text-sm font-medium text-[#1E293B]">Nom</label>
                <input type="text" id="contact-name" wire:model="name" autocomplete="name" required
                       class="w-full rounded-xl border border-[#E2E8F0] bg-[#FDFBF7] px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="contact-email" class="mb-1 block text-sm font-medium text-[#1E293B]">E-mail</label>
                <input type="email" id="contact-email" wire:model="email" autocomplete="email" required
                       class="w-full rounded-xl border border-[#E2E8F0] bg-[#FDFBF7] px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="contact-subject" class="mb-1 block text-sm font-medium text-[#1E293B]">Objet</label>
                <select id="contact-subject" wire:model="subject"
                        class="w-full rounded-xl border border-[#E2E8F0] bg-[#FDFBF7] px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20">
                    @foreach(\App\Livewire\Contact::SUBJECTS as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('subject') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="contact-message" class="mb-1 block text-sm font-medium text-[#1E293B]">Message</label>
                <textarea id="contact-message" wire:model="message" rows="6" required
                          class="w-full rounded-xl border border-[#E2E8F0] bg-[#FDFBF7] px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20"></textarea>
                @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Piège anti-robot : invisible pour les humains --}}
            <div class="absolute -left-[9999px]" aria-hidden="true">
                <label for="contact-website">Ne pas remplir</label>
                <input type="text" id="contact-website" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <button type="submit" wire:loading.attr="disabled" wire:target="send"
                    class="inline-flex min-h-[44px] items-center justify-center rounded-full bg-[#1E3D59] px-6 py-2.5 text-sm font-semibold text-[#FDFBF7] transition hover:bg-[#16293F] disabled:opacity-60 sm:self-start">
                <span wire:loading.remove wire:target="send">Envoyer</span>
                <span wire:loading wire:target="send">Envoi en cours...</span>
            </button>

            <p class="text-xs text-[#333333]/60">
                Ces informations servent uniquement à vous répondre.
                <a href="{{ route('legal.privacy') }}" wire:navigate class="underline underline-offset-2 hover:text-[#1E3D59]">Politique de confidentialité</a>
            </p>
        </form>
    @endif
</div>