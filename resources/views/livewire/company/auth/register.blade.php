@push('body-top')
    {{-- Fond Neat : fixe, couvre tout le viewport en permanence (donc aussi visible
         au niveau du footer une fois scrollé), uniquement sur la page d'accueil. --}}
    <canvas id="neat-home-background"
            class="fixed inset-0 z-0 h-screen w-screen"
            style="pointer-events: none;"
            aria-hidden="true"></canvas>
@endpush
  
  <div class="w-full z-10 max-w-md">
        {{-- Logo Dipla centré --}}
        <div class="mb-6 text-center">
            <a href="{{ route('home') }}" wire:navigate class="text-4xl font-extrabold tracking-tight text-[#1E293B] md:text-5xl">
                Dipla
            </a>
        </div>
    <div class="rounded-2xl border border-[#E2E8F0] bg-[#FAFAFF] p-6 shadow-sm">

        <div class="text-center">
            <h1 class="text-2xl font-semibold text-[#1E293B]">Inscription commerce</h1>
            <p class="text-sm text-[#333333] mt-1">Créez votre compte pour publier vos produits et services.</p>
        </div>

        <form wire:submit="register" class="flex flex-col gap-4"
            x-data="{ navigating: false }"
            x-on:livewire:navigate.window="navigating = true">
            <flux:input
                wire:model="name"
                label="Nom du commerce"
                type="text"
                required
                autofocus
                autocomplete="organization"
                class="rounded-xl! border-[#E2E8F0]! bg-[#FDFBF7]! focus:border-[#1E3D59]! focus:ring-[#1E3D59]/20!"
            />

            <flux:input
                wire:model="email"
                label="Email"
                type="email"
                required
                autocomplete="email"
                class="rounded-xl! border-[#E2E8F0]! bg-[#FDFBF7]! focus:border-[#1E3D59]! focus:ring-[#1E3D59]/20!"
            />

            <div wire:ignore>
                <div data-location-picker class="relative">
                    <label for="address" class="block text-sm font-medium text-[#1E293B] mb-1">Adresse</label>

                    <input type="text" id="address" required autocomplete="off"
                        data-role="address-input"
                        wire:model="address"
                        placeholder="Commence à taper une adresse..."
                        class="w-full rounded-xl border border-[#E2E8F0] bg-[#FDFBF7] px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20">

                    <div data-role="suggestions"
                        style="z-index: 9999;"
                        class="hidden absolute mt-1 w-full rounded-xl border border-[#E2E8F0] bg-[#FAFAFF] shadow-lg max-h-60 overflow-y-auto">
                    </div>

                    <div data-role="map" class="mt-3 h-56 w-full rounded-xl border border-[#E2E8F0] overflow-hidden"></div>

                    <p class="text-xs text-[#333333]/60 mt-1">Ajuste le repère sur la carte si besoin.</p>

                    <input type="hidden" data-role="latitude" wire:model="latitude">
                    <input type="hidden" data-role="longitude" wire:model="longitude">
                </div>
            </div>

            <flux:input
                wire:model="password"
                label="Mot de passe"
                type="password"
                required
                autocomplete="new-password"
                viewable
                class="rounded-xl! border-[#E2E8F0]! bg-[#FDFBF7]! focus:border-[#1E3D59]! focus:ring-[#1E3D59]/20!"
            />

            <flux:input
                wire:model="password_confirmation"
                label="Confirmer le mot de passe"
                type="password"
                required
                autocomplete="new-password"
                viewable
                class="rounded-xl! border-[#E2E8F0]! bg-[#FDFBF7]! focus:border-[#1E3D59]! focus:ring-[#1E3D59]/20!"
            />

            <div>
                <label class="flex cursor-pointer items-start gap-2.5 text-sm text-[#333333]">
                    <input type="checkbox" wire:model="acceptTerms" required
                        class="mt-0.5 h-4 w-4 shrink-0 cursor-pointer rounded border-[#E2E8F0] accent-[#1E3D59]">
                    <span>
                        J'ai lu et j'accepte les
                        <a href="{{ route('legal.terms') }}" target="_blank" rel="noopener"
                        class="font-medium text-[#1E3D59] underline underline-offset-2">conditions d'utilisation</a>.
                    </span>
                </label>
                @error('acceptTerms') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <flux:button type="submit" variant="primary" :loading="false"
                        wire:loading.attr="disabled" wire:target="register" x-bind:disabled="navigating"
                        class="w-full rounded-full! bg-[#1E3D59]! hover:bg-[#16293F]! text-[#FFFFFF]! font-semibold! disabled:opacity-60 disabled:cursor-not-allowed">
                <span class="inline-flex items-center justify-center gap-2">
                    <x-spinner wire:loading wire:target="register" />
                    <x-spinner x-show="navigating" x-cloak />
                    <span>Créer mon compte commerce</span>
                </span>
            </flux:button>
        </form>

        <div class="text-center text-sm text-[#333333] mt-4">
            Déjà un compte ?
            <flux:link :href="route('login', ['tab' => 'company'])" wire:navigate class="text-[#1E3D59]!">Se connecter</flux:link>
        </div>
    </div>
</div>