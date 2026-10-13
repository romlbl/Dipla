@push('body-top')
    <canvas id="neat-home-background"
            class="fixed inset-0 z-0 h-screen w-screen"
            style="pointer-events: none;"
            aria-hidden="true"></canvas>
@endpush

<x-layouts::guest>
    <div class="w-full z-10 max-w-md">
        <div class="mb-0.5 text-center">
            <a href="{{ route('home') }}" wire:navigate class="text-4xl font-extrabold tracking-tight text-[#1E293B] md:text-5xl">
                Dipla
            </a>
        </div>

        <div class="max-w-md mx-auto py-12" x-data="{ tab: '{{ $tab === 'company' ? 'company' : 'user' }}' }">

            <div class="flex rounded-full border border-[#E2E8F0] p-1 mb-8">
                <button @click="tab = 'user'"
                        :class="tab === 'user' ? 'bg-[#1E3D59] text-[#FDFBF7]' : 'text-[#333333]/50'"
                        class="flex-1 rounded-full px-4 py-2 text-sm font-medium transition">
                    Utilisateur
                </button>
                <button @click="tab = 'company'"
                        :class="tab === 'company' ? 'bg-[#1E3D59] text-[#FDFBF7]' : 'text-[#333333]/50'"
                        class="flex-1 rounded-full px-4 py-2 text-sm font-medium transition">
                    Commerce
                </button>
            </div>

            <div class="rounded-2xl border border-[#E2E8F0] bg-[#FAFAFF] p-6 shadow-sm">

                {{-- Onglet Utilisateur --}}
                <div x-show="tab === 'user'" x-cloak>
                    <div class="text-center">
                        <h1 class="text-2xl font-semibold text-[#1E293B]">Connexion</h1>
                    </div>
                    <x-auth-session-status class="mb-4 text-center" :status="session('status')" />
                    <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-4" x-data="{ submitting: false }" @submit="submitting = true" @pageshow.window="submitting = false">
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

                        <div class="relative">
                            <flux:input
                                name="password"
                                label="Mot de passe"
                                type="password"
                                required
                                autocomplete="current-password"
                                viewable
                                class="rounded-xl! border-[#E2E8F0]! bg-[#FDFBF7]! focus:border-[#1E3D59]! focus:ring-[#1E3D59]/20!"
                            />
                        </div>
                        <div class="text-right -mt-2">
                            <flux:link :href="route('password.request')" wire:navigate class="text-sm text-[#1E3D59]!">Mot de passe oublié ?</flux:link>
                        </div>

                        <flux:button variant="primary" type="submit" x-bind:disabled="submitting"
                                    class="w-full rounded-full! bg-[#1E3D59]! hover:bg-[#16293F]! text-[#FFFFFF]! font-semibold! disabled:opacity-60 disabled:cursor-not-allowed">
                            <span class="inline-flex items-center justify-center gap-2">
                                <x-spinner x-show="submitting" x-cloak />
                                <span x-text="submitting ? 'Connexion…' : 'Se connecter'">Se connecter</span>
                            </span>
                        </flux:button>
                    </form>

                    <div class="text-center text-sm text-[#333333] mt-4">
                        Pas encore de compte ?
                        <flux:link :href="route('register')" wire:navigate class="text-[#1E3D59]!">Inscription</flux:link>
                    </div>
                </div>

                {{-- Onglet Commerce --}}
                <div x-show="tab === 'company'" x-cloak>
                    <livewire:company.auth.login />
                </div>

            </div>
        </div>
    </div>
</x-layouts::guest>