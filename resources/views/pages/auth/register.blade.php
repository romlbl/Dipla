@push('body-top')
    {{-- Fond Neat : fixe, couvre tout le viewport en permanence (donc aussi visible
         au niveau du footer une fois scrollé), uniquement sur la page d'accueil. --}}
    <canvas id="neat-home-background"
            class="fixed inset-0 z-0 h-screen w-screen"
            style="pointer-events: none;"
            aria-hidden="true"></canvas>
@endpush
<x-layouts::guest>
    <div class="w-full z-10 max-w-md">
        {{-- Logo Dipla centré --}}
        <div class="mb-6 text-center">
            <a href="{{ route('home') }}" wire:navigate class="text-4xl font-extrabold tracking-tight text-[#1E293B] md:text-5xl">
                Dipla
            </a>
        </div>
        <div class="rounded-2xl border border-[#E2E8F0] bg-[#FAFAFF] p-6 shadow-sm">

            <div class="text-center mb-6">
                <h1 class="text-2xl font-semibold text-[#1E293B]">Créer un compte</h1>
                <p class="text-sm text-[#333333] mt-1">Rejoins Dipla pour découvrir les commerces autour de vous.</p>
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-4"
            x-data="{ submitting: false }" @submit="submitting = true" @pageshow.window="submitting = false">
                @csrf

                <flux:input
                    name="name"
                    label="Nom"
                    :value="old('name')"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Votre nom"
                    class="rounded-xl! border-[#E2E8F0]! bg-[#FDFBF7]! focus:border-[#1E3D59]! focus:ring-[#1E3D59]/20!"
                />

                <flux:input
                    name="email"
                    label="Email"
                    :value="old('email')"
                    type="email"
                    required
                    autocomplete="email"
                    placeholder="email@exemple.com"
                    class="rounded-xl! border-[#E2E8F0]! bg-[#FDFBF7]! focus:border-[#1E3D59]! focus:ring-[#1E3D59]/20!"
                />

                {{-- Adresse (facultative) --}}
                <div data-location-picker class="relative">
                    <label for="address" class="block text-sm font-medium text-[#1E293B] mb-1">
                        Adresse <span class="font-normal text-[#333333]/50">(facultatif)</span>
                    </label>

                    <input type="text" id="address" name="address" autocomplete="off"
                        data-role="address-input"
                        value="{{ old('address') }}"
                        placeholder="Commence à taper une adresse..."
                        class="w-full rounded-xl border border-[#E2E8F0] bg-[#FDFBF7] px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20">

                    <div data-role="suggestions" style="z-index: 9999;"
                        class="hidden absolute mt-1 w-full rounded-xl border border-[#E2E8F0] bg-[#FAFAFF] shadow-lg max-h-60 overflow-y-auto">
                    </div>

                    <div data-role="map" class="mt-3 h-56 w-full rounded-xl border border-[#E2E8F0] overflow-hidden"></div>

                    <p class="text-xs text-[#333333]/60 mt-1">Ajoutez votre adresse pour des résultats "à proximité" personnalisés — vous pouvez passer cette étape et l'ajouter plus tard.</p>

                    <input type="hidden" name="latitude" data-role="latitude" value="{{ old('latitude') }}">
                    <input type="hidden" name="longitude" data-role="longitude" value="{{ old('longitude') }}">
                </div>

                <flux:input
                    name="password"
                    label="Mot de passe"
                    type="password"
                    required
                    autocomplete="new-password"
                    passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                    viewable
                    class="rounded-xl! border-[#E2E8F0]! bg-[#FDFBF7]! focus:border-[#1E3D59]! focus:ring-[#1E3D59]/20!"
                />

                <flux:input
                    name="password_confirmation"
                    label="Confirmer le mot de passe"
                    type="password"
                    required
                    autocomplete="new-password"
                    passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                    viewable
                    class="rounded-xl! border-[#E2E8F0]! bg-[#FDFBF7]! focus:border-[#1E3D59]! focus:ring-[#1E3D59]/20!"
                />

                <flux:button variant="primary" type="submit" data-test="register-user-button" x-bind:disabled="submitting"
                            class="w-full rounded-full! bg-[#1E3D59]! hover:bg-[#16293F]! font-semibold! text-[#FDFBF7]! disabled:opacity-60 disabled:cursor-not-allowed">
                    <span class="inline-flex items-center justify-center gap-2">
                        <x-spinner x-show="submitting" x-cloak />
                        <span x-text="submitting ? 'Création…' : 'Créer mon compte'">Créer mon compte</span>
                    </span>
                </flux:button>
            </form>

            <div class="text-center text-sm text-[#333333] mt-4">
                Déjà un compte ?
                <flux:link :href="route('login')" wire:navigate class="text-[#1E3D59]!">Se connecter</flux:link>
            </div>
        </div>
    </div>
</x-layouts::guest>