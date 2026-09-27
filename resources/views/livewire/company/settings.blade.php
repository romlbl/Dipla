<div x-data="{ confirmingDelete: false }">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-[#1E293B]">Paramètres</h1>
        <p class="text-sm text-[#333333]/70 mt-1">Gèrez votre adresse email, votre mot de passe et votre compte.</p>
    </div>

    @if(session('settings-status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 mb-6">
            {{ session('settings-status') }}
        </div>
    @endif

    <div class="flex flex-col gap-6 max-w-xl">

        {{-- Adresse email --}}
        <div class="rounded-2xl border border-[#E2E8F0] bg-[#FAFAFF] p-6 shadow-sm">
            <h2 class="text-base font-semibold text-[#1E293B] mb-4">Adresse email</h2>

            <form wire:submit="updateEmail" class="flex flex-col gap-4">
                <div>
                    <label for="settings-email" class="block text-sm font-medium text-[#1E293B] mb-1">Nouvel email</label>
                    <input type="email" id="settings-email" wire:model="email"
                           class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20">
                    @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="settings-email-password" class="block text-sm font-medium text-[#1E293B] mb-1">Mot de passe actuel</label>
                    <input type="password" id="settings-email-password" wire:model="current_password_email"
                           class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20">
                    @error('current_password_email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" wire:loading.attr="disabled" wire:target="updateEmail"
                        class="self-start inline-flex items-center justify-center gap-2 rounded-full bg-[#1E3D59] px-5 py-2 text-sm font-semibold text-[#FDFBF7] hover:bg-[#16293F] transition disabled:cursor-not-allowed disabled:opacity-60">
                    <x-spinner wire:loading wire:target="updateEmail" />
                        Mettre à jour l'email
                </button>
            </form>
        </div>

        {{-- Mot de passe --}}
        <div class="rounded-2xl border border-[#E2E8F0] bg-[#FAFAFF] p-6 shadow-sm">
            <h2 class="text-base font-semibold text-[#1E293B] mb-4">Mot de passe</h2>

            <form wire:submit="updatePassword" class="flex flex-col gap-4">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-[#1E293B] mb-1">Mot de passe actuel</label>
                    <input type="password" id="current_password" wire:model="current_password"
                           class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20">
                    @error('current_password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="new_password" class="block text-sm font-medium text-[#1E293B] mb-1">Nouveau mot de passe</label>
                    <input type="password" id="new_password" wire:model="password"
                           class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20">
                    @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="new_password_confirmation" class="block text-sm font-medium text-[#1E293B] mb-1">Confirmer le nouveau mot de passe</label>
                    <input type="password" id="new_password_confirmation" wire:model="password_confirmation"
                           class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20">
                </div>

                <button type="submit" wire:loading.attr="disabled" wire:target="updatePassword"
                        class="self-start inline-flex items-center justify-center gap-2 rounded-full bg-[#1E3D59] px-5 py-2 text-sm font-semibold text-[#FDFBF7] hover:bg-[#16293F] transition disabled:cursor-not-allowed disabled:opacity-60">
                    <x-spinner wire:loading wire:target="updatePassword" />
                    Mettre à jour le mot de passe
                </button>
            </form>
        </div>

        {{-- Contact --}}
        <div class="rounded-2xl border border-[#E2E8F0] bg-[#FAFAFF] p-6 shadow-sm">
            <h2 class="text-base font-semibold text-[#1E293B] mb-2">Nous contacter</h2>
            <p class="text-sm text-[#333333]/70 mb-4">Un souci technique, une question sur votre compte ? Fais-le nous savoir.</p>

            <a href="{{ route('contact', ['motif' => 'bug']) }}" wire:navigate
               class="inline-flex items-center justify-center rounded-full border border-[#E2E8F0] px-5 py-2 text-sm font-semibold text-[#1E293B] hover:bg-white transition">
                Ouvrir le formulaire de contact
            </a>
        </div>

        {{-- Zone dangereuse --}}
        <div class="rounded-2xl border border-red-200 bg-red-50/40 p-6 shadow-sm">
            <h2 class="text-base font-semibold text-red-700 mb-2">Supprimer mon compte</h2>
            <p class="text-sm text-red-700/80 mb-4">
                Cette action est définitive. Elle supprime votre compte, tous vos produits, leurs photos, ainsi que les avis et questions associés.
            </p>

            <button type="button" @click="confirmingDelete = true"
                    class="inline-flex items-center justify-center rounded-full bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-700 transition">
                Supprimer définitivement mon compte
            </button>
        </div>
    </div>

    {{-- Modale de confirmation --}}
    <div x-show="confirmingDelete" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
         style="display: none;">
        <div @click.outside="confirmingDelete = false"
             class="w-full max-w-md rounded-2xl bg-[#FDFBF7] p-6 shadow-lg">
            <h3 class="text-base font-semibold text-[#1E293B]">Confirmer la suppression du compte</h3>
            <p class="text-sm text-[#333333] mt-1">
                Cette action est irréversible. Saisissez votre mot de passe pour confirmer.
            </p>

            <form wire:submit="deleteAccount" class="mt-4 flex flex-col gap-3">
                <input type="password" wire:model="delete_password" placeholder="Mot de passe"
                       class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-2.5 text-sm text-[#333333] focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20">
                @error('delete_password') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                <div class="flex gap-3 mt-2">
                    <button type="button" @click="confirmingDelete = false"
                            class="flex-1 rounded-full border border-[#E2E8F0] px-4 py-2 text-sm font-medium text-[#1E293B] hover:bg-white transition">
                        Annuler
                    </button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="deleteAccount"
                            class="flex-1 inline-flex items-center justify-center gap-2 rounded-full bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition disabled:cursor-not-allowed disabled:opacity-60">
                        <x-spinner wire:loading wire:target="deleteAccount" />
                        Supprimer définitivement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>