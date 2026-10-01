<div x-data="productImageManager()">
    <label class="block text-sm font-medium text-[#1E293B] mb-2">Photos</label>

    <div data-role="sortable-images"
         x-init="Sortable.create($el, {
             animation: 150,
             onEnd: () => {
                 const ids = [...$el.children].map(c => c.dataset.id);
                 $wire.reorder(ids);
             }
         })"
         class="grid grid-cols-3 sm:grid-cols-4 gap-3 mb-4">
        @foreach($images as $image)
            <div wire:key="image-{{ $image->id }}" data-id="{{ $image->id }}" class="relative group cursor-move">
                <img src="{{ $image->url }}" alt="" class="aspect-[2/3] w-full object-cover rounded-xl border border-[#E2E8F0]">
                <button type="button" @click="confirmingDelete = {{ $image->id }}"
                        class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-white/90 text-red-600 text-xs font-bold flex items-center justify-center shadow opacity-0 group-hover:opacity-100 transition">
                    ✕
                </button>
            </div>
        @endforeach
    </div>

    <label class="inline-flex items-center gap-2 rounded-full border border-[#E2E8F0] px-4 py-2 text-sm font-medium text-[#1E293B] cursor-pointer hover:bg-[#FDFBF7] transition">
        + Ajouter des photos
        <input type="file" x-ref="fileInput" multiple accept="image/*" class="hidden"
               @change="handleFiles($event.target.files)">
    </label>

    <div wire:loading wire:target="newImages" role="status" class="mt-3">
        <div class="flex items-center gap-2 text-sm text-[#333333]/70">
            <x-spinner class="text-[#1E3D59]" />
            <span>Envoi des photos…</span>
        </div>
        <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-[#E2E8F0]">
            <div class="h-full bg-[#1E3D59] transition-all" :style="`width: ${uploadProgress}%`"></div>
        </div>
    </div>

    <p x-show="errorMessage" x-text="errorMessage" x-cloak class="mt-2 text-sm text-red-600"></p>
    @error('newImages.*') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
    @error('newImages') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror

    {{-- Modale de recadrage --}}
    <div x-show="cropModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
         style="display: none;">
        <div class="bg-[#FDFBF7] rounded-2xl p-6 max-w-md w-full shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-semibold text-[#1E293B]">Recadrer la photo</h3>
                <span class="text-sm text-[#333333]/60" x-text="`${queueIndex + 1} / ${queue.length}`"></span>
            </div>

            <div class="relative min-h-40 max-h-80 overflow-hidden rounded-xl">
                <img x-ref="cropImage" class="max-w-full block">
                <div x-show="cropLoading" x-cloak role="status"
                    class="absolute inset-0 flex items-center justify-center bg-[#FDFBF7]/80">
                    <x-spinner size="h-8 w-8" class="text-[#1E3D59]" />
                    <span class="sr-only">Chargement de l'image</span>
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button type="button" @click="cancelCropping()"
                        class="rounded-full border border-[#E2E8F0] px-4 py-2 text-sm font-medium text-[#1E293B] hover:bg-white transition">
                    Annuler tout
                </button>
                <button type="button" @click="skipImage()"
                        class="rounded-full border border-[#E2E8F0] px-4 py-2 text-sm font-medium text-[#1E293B] hover:bg-white transition">
                    Passer
                </button>
                <button type="button" @click="confirmCrop()" :disabled="cropLoading || processing"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-full bg-[#1E3D59] px-4 py-2 text-sm font-semibold text-[#FDFBF7] hover:bg-[#16293F] transition disabled:cursor-not-allowed disabled:opacity-60">
                    <x-spinner x-show="processing" x-cloak />
                    Valider ce recadrage
                </button>
            </div>
        </div>
    </div>

    {{-- Modale de suppression --}}
    <div x-show="confirmingDelete !== null" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
         style="display: none;">
        <div @click.outside="confirmingDelete = null"
             class="bg-[#FDFBF7] rounded-2xl p-6 max-w-sm w-full shadow-lg">
            <h3 class="text-base font-semibold text-[#1E293B]">Supprimer cette photo ?</h3>
            <p class="text-sm text-[#333333] mt-1">Cette action est définitive.</p>

            <div class="flex gap-3 mt-5">
                <button type="button" @click="confirmingDelete = null"
                        class="flex-1 rounded-full border border-[#E2E8F0] px-4 py-2 text-sm font-medium text-[#1E293B] hover:bg-white transition">
                    Annuler
                </button>
                <button type="button" @click="$wire.deleteImage(confirmingDelete); confirmingDelete = null"
                        class="flex-1 rounded-full bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition">
                    Supprimer
                </button>
            </div>
        </div>
    </div>
</div>