<div class="w-full max-w-md z-10">
    <div class="mb-6 text-center">
        <a href="{{ route('home') }}" wire:navigate class="text-4xl font-extrabold tracking-tight text-[#1E293B] md:text-5xl">Dipla</a>
    </div>

    <div class="rounded-2xl border border-[#E2E8F0] bg-[#FAFAFF] p-6 shadow-sm">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-semibold text-[#1E293B]">Mot de passe oublié</h1>
            <p class="text-sm text-[#333333] mt-1">Compte commerce : entrez votre email, un lien vous sera envoyé.</p>
        </div>

        @if($sent)
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ __('passwords.sent') }}
            </div>
        @else
            <form wire:submit="send" class="flex flex-col gap-4">
                <flux:input
                    wire:model="email"
                    label="Email"
                    type="email"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="email@exemple.com"
                    class="rounded-xl! border-[#E2E8F0]! bg-[#FDFBF7]! focus:border-[#1E3D59]! focus:ring-[#1E3D59]/20!"
                />
                @error('email') <p class="text-sm text-red-600 -mt-2">{{ $message }}</p> @enderror

                <flux:button variant="primary" type="submit" :loading="false"
                             wire:loading.attr="disabled" wire:target="send"
                             class="w-full rounded-full! bg-[#1E3D59]! hover:bg-[#16293F]! font-semibold! text-[#FDFBF7]! disabled:opacity-60 disabled:cursor-not-allowed">
                    <span class="inline-flex items-center justify-center gap-2">
                        <x-spinner wire:loading wire:target="send" />
                        <span>Envoyer le lien</span>
                    </span>
                </flux:button>
            </form>
        @endif

        <div class="text-center text-sm text-[#333333] mt-4">
            <flux:link :href="route('login', ['tab' => 'company'])" wire:navigate class="text-[#1E3D59]!">Retour à la connexion</flux:link>
        </div>
    </div>
</div>