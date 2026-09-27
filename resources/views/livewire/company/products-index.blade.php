<div x-data="{ confirmingDeleteId: null }">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-[#1E293B]">Catalogue Produits</h1>
            <p class="text-sm text-[#333333]/70 mt-1">Gérez votre inventaire et vos offres locales.</p>
        </div>
        <a href="{{ route('company.products.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-full bg-[#1E3D59] px-5 py-2.5 text-sm font-semibold text-[#FDFBF7] transition hover:bg-[#16293F]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Ajouter un produit
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filtres --}}
    <div class="flex flex-col md:flex-row gap-3 mb-6 p-4 rounded-xl border border-[#E2E8F0] bg-[#FAFAFF] shadow-sm">
        <div class="relative flex-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#333333]/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
            </svg>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Rechercher un produit..."
                   class="w-full rounded-full border border-[#E2E8F0] bg-white pl-11 pr-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/10">
        </div>

        <div class="flex gap-3 overflow-x-auto">
            <select wire:model.live="type"
                    class="rounded-full border border-[#E2E8F0] bg-white px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none min-w-[150px]">
                <option value="">Tous types</option>
                <option value="produit">Produits</option>
                <option value="service">Services</option>
            </select>

            <select wire:model.live="sort"
                    class="rounded-full border border-[#E2E8F0] bg-white px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none min-w-[150px]">
                <option value="recent">Plus récents</option>
                <option value="price_asc">Prix croissant</option>
                <option value="price_desc">Prix décroissant</option>
            </select>
        </div>
    </div>

    {{-- Grille produits --}}
    @if($products->isEmpty())
        <div class="rounded-2xl border border-dashed border-[#E2E8F0] p-10 text-center">
            <p class="text-[#333333]">Aucun produit ne correspond à votre recherche.</p>
            <a href="{{ route('company.products.create') }}" class="text-sm text-[#1E3D59] font-medium hover:underline mt-2 inline-block">
                Publier votre premier produit
            </a>
        </div>
    @else
        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-6">
            @foreach($products as $product)
                <div wire:key="product-{{ $product->id }}"
                     class="group flex flex-col overflow-hidden rounded-xl border border-[#E2E8F0] bg-[#FAFAFF] shadow-sm transition hover:shadow-md">
                    <div class="relative aspect-[2/3] w-full bg-[#E2E8F0]">
                        @if($product->images->isNotEmpty())
                            <img src="{{ $product->images->first()->url }}" alt="{{ $product->title }}"
                                 class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-sm text-[#333333]/40">
                                Pas d'image
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-1 flex-col p-3 sm:p-4">
                        <h3 class="text-sm sm:text-base font-semibold text-[#1E293B] line-clamp-2">{{ $product->title }}</h3>

                        @if($product->reviews->isNotEmpty())
                            <div class="mt-1 flex items-center gap-1 text-xs sm:text-sm text-[#333333]/70">
                                <span class="text-amber-500">★</span>
                                <span>{{ $product->averageRating() }}</span>
                                <span class="text-[#333333]/40">({{ $product->reviews->count() }})</span>
                            </div>
                        @else
                            <p class="mt-1 text-xs text-[#333333]/40">Aucun avis</p>
                        @endif

                        <p class="mt-2 sm:mt-3 font-mono text-base sm:text-lg font-semibold text-[#1E293B]">
                            {{ $product->priceLabel() }}
                        </p>

                        <div class="mt-auto pt-3 sm:pt-4 flex gap-2">
                            <a href="{{ route('company.products.edit', $product) }}"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-full border border-[#E2E8F0] py-1.5 sm:py-2 text-xs sm:text-sm font-medium text-[#1E3D59] transition hover:bg-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                </svg>
                                Éditer
                            </a>
                            <button type="button"
                                    @click="confirmingDeleteId = {{ $product->id }}"
                                    aria-label="Supprimer {{ $product->title }}"
                                    class="inline-flex items-center justify-center rounded-full border border-[#E2E8F0] px-2.5 sm:px-3 py-1.5 sm:py-2 text-red-600 transition hover:bg-red-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </button>
                    </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $products->links() }}
        </div>
    @endif
        {{-- Modale de confirmation de suppression --}}
    <div x-show="confirmingDeleteId !== null" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
         style="display: none;">
        <div @click.outside="confirmingDeleteId = null"
             class="w-full max-w-sm rounded-2xl bg-[#FDFBF7] p-6 shadow-lg">
            <h3 class="text-base font-semibold text-[#1E293B]">Supprimer ce produit ?</h3>
            <p class="text-sm text-[#333333] mt-1">Cette action est définitive et supprimera aussi ses photos, avis et questions associés.</p>

            <div class="flex gap-3 mt-5">
                <button type="button" @click="confirmingDeleteId = null"
                        class="flex-1 rounded-full border border-[#E2E8F0] px-4 py-2 text-sm font-medium text-[#1E293B] hover:bg-white transition">
                    Annuler
                </button>
                <button type="button"
                        @click="$wire.deleteProduct(confirmingDeleteId); confirmingDeleteId = null"
                        class="flex-1 rounded-full bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition">
                    Supprimer
                </button>
            </div>
        </div>
    </div>
</div>