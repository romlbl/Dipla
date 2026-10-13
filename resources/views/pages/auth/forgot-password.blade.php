<x-layouts::guest>
    <div class="w-full max-w-md z-10">
        <div class="mb-6 text-center">
            <a href="{{ route('home') }}" wire:navigate class="text-4xl font-extrabold tracking-tight text-[#1E293B] md:text-5xl">Dipla</a>
        </div>

        <div class="rounded-2xl border border-[#E2E8F0] bg-[#FAFAFF] p-6 shadow-sm">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-semibold text-[#1E293B]">Mot de passe oublié</h1>
                <p class="text-sm text-[#333333] mt-1">Entrez votre email, un lien vous sera envoyé.</p>
            </div>

            <x-auth-session-status class="mb-4 text-center" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4"
                  x-data="{ submitting: false }" @submit="submitting = true" @pageshow.window="submitting = false">
                @csrf

                <flux:input
                    name="email"
                    label="Email"
                    :value="old('email')"
                    type="email"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="email@exemple.com"
                    class="rounded-xl! border-[#E2E8F0]! bg-[#FDFBF7]! focus:border-[#1E3D59]! focus:ring-[#1E3D59]/20!"
                />

                <flux:button variant="primary" type="submit" x-bind:disabled="submitting"
                             class="w-full rounded-full! bg-[#1E3D59]! hover:bg-[#16293F]! font-semibold! text-[#FDFBF7]! disabled:opacity-60 disabled:cursor-not-allowed">
                    <span class="inline-flex items-center justify-center gap-2">
                        <x-spinner x-show="submitting" x-cloak />
                        <span x-text="submitting ? 'Envoi…' : 'Envoyer le lien'">Envoyer le lien</span>
                    </span>
                </flux:button>
            </form>

            <div class="text-center text-sm text-[#333333] mt-4">
                <flux:link :href="route('login')" wire:navigate class="text-[#1E3D59]!">Retour à la connexion</flux:link>
            </div>
        </div>
    </div>
</x-layouts::guest>