<x-layouts::guest>
    <div class="w-full max-w-md z-10">
        <div class="mb-6 text-center">
            <a href="{{ route('home') }}" wire:navigate class="text-4xl font-extrabold tracking-tight text-[#1E293B] md:text-5xl">Dipla</a>
        </div>

        <div class="rounded-2xl border border-[#E2E8F0] bg-[#FAFAFF] p-6 shadow-sm">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-semibold text-[#1E293B]">Nouveau mot de passe</h1>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-4"
                  x-data="{ submitting: false }" @submit="submitting = true" @pageshow.window="submitting = false">
                @csrf
                <input type="hidden" name="token" value="{{ request()->route('token') }}">

                <flux:input
                    name="email"
                    label="Email"
                    value="{{ request('email') }}"
                    type="email"
                    required
                    readonly
                    autocomplete="email"
                    class="rounded-xl! border-[#E2E8F0]! bg-[#FDFBF7]!"
                />

                <flux:input
                    name="password"
                    label="Nouveau mot de passe"
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

                <flux:button variant="primary" type="submit" x-bind:disabled="submitting"
                             class="w-full rounded-full! bg-[#1E3D59]! hover:bg-[#16293F]! font-semibold! text-[#FDFBF7]! disabled:opacity-60 disabled:cursor-not-allowed">
                    <span class="inline-flex items-center justify-center gap-2">
                        <x-spinner x-show="submitting" x-cloak />
                        <span x-text="submitting ? 'Enregistrement…' : 'Changer le mot de passe'">Changer le mot de passe</span>
                    </span>
                </flux:button>
            </form>
        </div>
    </div>
</x-layouts::guest>