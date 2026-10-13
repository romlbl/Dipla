<div class="w-full max-w-md z-10">
    <div class="mb-6 text-center">
        <a href="{{ route('home') }}" wire:navigate class="text-4xl font-extrabold tracking-tight text-[#1E293B] md:text-5xl">Dipla</a>
    </div>

    <div class="rounded-2xl border border-[#E2E8F0] bg-[#FAFAFF] p-6 shadow-sm">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-semibold text-[#1E293B]">Nouveau mot de passe</h1>
        </div>

        <form wire:submit="resetPassword" class="flex flex-col gap-4">
            <flux:input
                wire:model="email"
                label="Email"
                type="email"
                required
                readonly
                autocomplete="email"
                class="rounded-xl! border-[#E2E8F0]! bg-[#FDFBF7]!"
            />
            @error('email') <p class="text-sm text-red-600 -mt-2">{{ $message }}</p> @enderror

            <flux:input
                wire:model="password"
                label="Nouveau mot de passe"
                type="password"
                required
                autocomplete="new-password"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
                class="rounded-xl! border-[#E2E8F0]! bg-[#FDFBF7]! focus:border-[#1E3D59]! focus:ring-[#1E3D59]/20!"
            />
            @error('password') <p class="text-sm text-red-600 -mt-2">{{ $message }}</p> @enderror

            <flux:input
                wire:model="password_confirmation"
                label="Confirmer le mot de passe"
                type="password"
                required
                autocomplete="new-password"
                viewable
                class="rounded-xl! border-[#E2E8F0]! bg-[#FDFBF7]! focus:border-[#1E3D59]! focus:ring-[#1E3D59]/20!"
            />

            <flux:button variant="primary" type="submit" :loading="false"
                         wire:loading.attr="disabled" wire:target="resetPassword"
                         class="w-full rounded-full! bg-[#1E3D59]! hover:bg-[#16293F]! font-semibold! text-[#FDFBF7]! disabled:opacity-60 disabled:cursor-not-allowed">
                <span class="inline-flex items-center justify-center gap-2">
                    <x-spinner wire:loading wire:target="resetPassword" />
                    <span>Changer le mot de passe</span>
                </span>
            </flux:button>
        </form>
    </div>
</div>