<div
    x-data="{ locating: false, locationError: false }"
    x-init="
        if ($wire.userLat && $wire.userLng) {
            locating = false;
        } else if ($store.searchLocation.hasLocation) {
            $wire.setUserLocation($store.searchLocation.lat, $store.searchLocation.lng);
            locating = false;
        } else {
            locating = true;
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        $wire.setUserLocation(position.coords.latitude, position.coords.longitude);
                        locating = false;
                    },
                    () => { locationError = true; locating = false; },
                    { timeout: 8000 }
                );
            } else {
                locationError = true;
                locating = false;
            }
        }

        $watch('$store.searchLocation.lat', () => {
            if ($store.searchLocation.hasLocation) {
                $wire.setUserLocation($store.searchLocation.lat, $store.searchLocation.lng);
            }
        });

        $watch('$store.searchLocation.lng', () => {
            if ($store.searchLocation.hasLocation) {
                $wire.setUserLocation($store.searchLocation.lat, $store.searchLocation.lng);
            }
        });
    "
>
    {{-- Bascule des modes --}}
    <div class="flex gap-2 mb-6 overflow-x-auto pb-1">
        <button wire:click="setMode('keyword')"
                class="shrink-0 rounded-full px-5 py-2 text-sm font-medium transition {{ $mode === 'keyword' ? 'bg-[#1E3D59] text-[#FDFBF7]' : 'border border-[#E2E8F0] text-[#1E293B] hover:bg-[#FAFAFF]' }}">
            Recherche
        </button>
        <button wire:click="setMode('discover')"
                class="shrink-0 rounded-full px-5 py-2 text-sm font-medium transition {{ $mode === 'discover' ? 'bg-[#1E3D59] text-[#FDFBF7]' : 'border border-[#E2E8F0] text-[#1E293B] hover:bg-[#FAFAFF]' }}">
            Découvrir
        </button>
    </div>

    {{-- Statut géolocalisation --}}
    <div x-show="locating" x-cloak role="status" class="mb-4 flex items-center gap-2 text-sm text-[#333333]/60">
        <x-spinner class="text-[#1E3D59]" /> Localisation en cours…
    </div>
    <div x-show="locationError" class="text-sm text-[#4A3B5C] mb-4">
        Position non disponible — le mode "à proximité" en a besoin, mais vous pouvez chercher par mot-clé.
    </div>

    <div class="flex flex-col md:flex-row gap-6 md:gap-8">

        {{-- Sidebar filtres --}}
        <aside class="w-full md:w-72 md:shrink-0">
            <div class="rounded-2xl border border-[#E2E8F0] bg-[#FAFAFF] p-5 shadow-sm md:sticky md:top-24 flex flex-col gap-5">

                {{-- Champ recherche (mode mot-clé uniquement) --}}
                @if($mode === 'keyword')
                    <div>
                        <label for="q" class="block text-sm font-medium text-[#1E293B] mb-1">Recherche</label>
                        <input type="text" id="q" wire:model.live.debounce.400ms="q"
                               placeholder="boulangerie, réparation vélo..."
                               class="w-full rounded-xl border border-[#E2E8F0] bg-[#FDFBF7] px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20">
                    </div>
                @endif

                {{-- Lieu de recherche : autocomplete Nominatim, synchronisé avec le store partagé --}}
                <div x-data="locationInlineSearch()" class="relative">
                    <label for="location-inline" class="block text-sm font-medium text-[#1E293B] mb-1">Lieu de recherche</label>

                    <div class="relative">
                        <input type="text" id="location-inline" x-model="query" @input="onQueryInput()" @focus="focused = true" @blur="focused = false; closeSuggestions()" @keydown.escape="closeSuggestions()" autocomplete="off"
                               placeholder="Ville, adresse, quartier..."
                               class="w-full rounded-xl border border-[#E2E8F0] bg-[#FDFBF7] pl-4 pr-8 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20">

                        <button type="button" x-show="query" x-cloak @click="clearLocation()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#333333]/40 hover:text-[#333333]">
                            ✕
                        </button>
                    </div>

                    <div x-show="suggestions.length > 0" x-cloak @mousedown.prevent
                         class="absolute z-20 mt-1 w-full rounded-xl border border-[#E2E8F0] bg-[#FAFAFF] shadow-lg max-h-60 overflow-y-auto">
                        <template x-for="suggestion in suggestions" :key="suggestion.place_id">
                            <button type="button" @click="selectSuggestion(suggestion)"
                                    class="block w-full px-4 py-2 text-left text-sm text-[#333333] hover:bg-[#FDFBF7] transition"
                                    x-text="suggestion.display_name"></button>
                        </template>
                    </div>

                    <button type="button" @click="useMyPosition()"
                            class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-[#1E3D59] hover:underline">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Utiliser ma position actuelle
                    </button>
                </div>

                {{-- Catégorie / Distance / Prix : un seul groupe déroulable en mobile --}}
                <div x-data="{ filtersOpen: false }" class="border-t border-[#E2E8F0] pt-5">
                    <button type="button" @click="filtersOpen = !filtersOpen"
                            class="flex w-full items-center justify-between md:pointer-events-none">
                        <span class="text-sm font-medium text-[#1E293B]">Filtres</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#333333]/50 transition-transform md:hidden"
                             :class="filtersOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div :class="filtersOpen ? '' : 'hidden md:block'" class="mt-4 flex flex-col gap-5">

                        {{-- Catégorie --}}
                        <div>
                            <span class="block text-sm font-medium text-[#1E293B] mb-2">Catégorie</span>
                            <div class="flex flex-wrap gap-2">
                                <button wire:click="$set('type', '')"
                                        class="rounded-full px-4 py-1.5 text-sm font-medium transition {{ $type === '' ? 'bg-[#1E3D59] text-[#FDFBF7]' : 'border border-[#E2E8F0] text-[#1E293B] hover:bg-white' }}">
                                    Tout
                                </button>
                                <button wire:click="$set('type', 'produit')"
                                        class="rounded-full px-4 py-1.5 text-sm font-medium transition {{ $type === 'produit' ? 'bg-[#1E3D59] text-[#FDFBF7]' : 'border border-[#E2E8F0] text-[#1E293B] hover:bg-white' }}">
                                    Produits
                                </button>
                                <button wire:click="$set('type', 'service')"
                                        class="rounded-full px-4 py-1.5 text-sm font-medium transition {{ $type === 'service' ? 'bg-[#1E3D59] text-[#FDFBF7]' : 'border border-[#E2E8F0] text-[#1E293B] hover:bg-white' }}">
                                    Services
                                </button>
                                <button wire:click="$set('type', 'commerce')"
                                        class="rounded-full px-4 py-1.5 text-sm font-medium transition {{ $type === 'commerce' ? 'bg-[#1E3D59] text-[#FDFBF7]' : 'border border-[#E2E8F0] text-[#1E293B] hover:bg-white' }}">
                                    Commerces
                                </button>
                            </div>
                        </div>
                        <div class="mb-4"></div>

                        {{-- Distance max --}}
                        @if($mode !== 'nearby')
                            <div x-data="{ value: @js($maxDistance) }" class="border-t border-[#E2E8F0] pt-5">
                                <div class="flex justify-between items-center mb-1">
                                    <label for="maxDistance" class="text-sm font-medium text-[#1E293B]">Distance max</label>
                                    <span class="text-sm font-mono text-[#1E293B]" x-text="value ? value + ' km' : 'Aucune'"></span>
                                </div>
                                <input type="range" id="maxDistance" min="1" max="200" step="1"
                                    x-model.number="value"
                                    @input.debounce.300ms="$wire.$set('maxDistance', value)"
                                    class="accent-[#1E3D59] w-full">
                            </div>
                        @endif

                        {{-- Prix max --}}
                        @if($type !== 'commerce')
                            <div x-data="{ value: @js($maxPrice) }" class="border-t border-[#E2E8F0] pt-5">
                                <div class="flex justify-between items-center mb-1">
                                    <label for="maxPrice" class="text-sm font-medium text-[#1E293B]">Prix max</label>
                                    <span class="text-sm font-mono text-[#1E293B]" x-text="value ? value + ' €' : 'Aucun'"></span>
                                </div>
                                <input type="range" id="maxPrice" min="5" max="1000" step="5"
                                    x-model.number="value"
                                    @input.debounce.300ms="$wire.$set('maxPrice', value)"
                                    class="accent-[#1E3D59] w-full">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </aside>

        {{-- Résultats --}}
        <div class="flex-1 min-w-0 flex flex-col gap-10 transition-opacity"
            wire:loading.class="opacity-50"
            wire:target="q,type,maxPrice,maxDistance,mode,setMode,setUserLocation,gotoPage,nextPage,previousPage">

            <div wire:loading.flex
                wire:target="q,type,maxPrice,maxDistance,mode,setMode,setUserLocation,gotoPage,nextPage,previousPage"
                role="status" class="items-center gap-2 text-sm text-[#333333]/60">
                <x-spinner class="text-[#1E3D59]" /> Recherche en cours…
            </div>

            {{-- Produits / Services --}}
            @if($products !== null)
                <div>
                    @if($type === '')
                        <h2 class="text-lg font-semibold text-[#1E293B] mb-4">Produits &amp; Services</h2>
                    @endif

                    @if($mode === 'nearby' && !$userLat)
                        <div class="rounded-2xl border border-dashed border-[#E2E8F0] p-10 text-center">
                            <p class="text-[#333333]">Activez votre position pour voir ce qu'il y a autour de vous.</p>
                        </div>
                    @elseif($products->isEmpty())
                        <div class="rounded-2xl border border-dashed border-[#E2E8F0] p-10 text-center">
                            <p class="text-[#333333]">Aucun résultat pour cette recherche.</p>
                        </div>
                    @else
                        <p class="text-sm text-[#333333]/60 mb-4">
                            {{ $products->total() }} résultat{{ $products->total() > 1 ? 's' : '' }}
                        </p>

                        <div class="flex flex-wrap gap-4 sm:gap-6">
                            @foreach($products as $product)
                                @include('partials.product-card', ['product' => $product])
                            @endforeach
                        </div>

                        <div class="mt-8">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            @endif

            {{-- Commerces : affichés dès que $companies est chargé (type "commerce" OU "Tout") --}}
            @if($companies !== null)
                <div>
                    @if($type === '')
                        <h2 class="text-lg font-semibold text-[#1E293B] mb-4">Commerces</h2>
                    @endif

                    @if($mode === 'nearby' && !$userLat)
                        <div class="rounded-2xl border border-dashed border-[#E2E8F0] p-10 text-center">
                            <p class="text-[#333333]">Activez votre position pour voir les commerces autour de vous.</p>
                        </div>
                    @elseif($companies->isEmpty())
                        @if($type === 'commerce')
                            <div class="rounded-2xl border border-dashed border-[#E2E8F0] p-10 text-center">
                                <p class="text-[#333333]">Aucun commerce ne correspond à cette recherche.</p>
                            </div>
                        @endif
                    @else
                        <p class="text-sm text-[#333333]/60 mb-4">
                            {{ $companies->total() }} commerce{{ $companies->total() > 1 ? 's' : '' }}
                        </p>

                        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                            @foreach($companies as $company)
                                <a href="{{ route('company.storefront', $company) }}" wire:navigate
                                   class="group flex flex-col overflow-hidden rounded-xl border border-[#E2E8F0] bg-[#FAFAFF] shadow-sm transition hover:shadow-md">
                                    <div class="relative aspect-[2/3] w-full bg-[#E2E8F0]">
                                        @php($cardImage = $company->card_image_url ?? $company->cover_image_url)
                                        @if($cardImage)
                                            <img src="{{ $cardImage }}" alt="{{ $company->name }}"
                                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-sm text-[#333333]/40">
                                                Pas d'image
                                            </div>
                                        @endif
                                        @if($company->reviews_count > 0)
                                            <span class="absolute bottom-2 left-2 inline-flex items-center gap-1 rounded-full bg-[#FDFBF7]/90 px-2.5 py-1 text-xs font-semibold text-[#1E293B] backdrop-blur-sm">
                                                <span class="text-[#4A3B5C]">★</span> {{ number_format($company->reviews_avg_rating, 1) }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex flex-1 flex-col p-4">
                                        <h3 class="font-semibold text-[#1E293B] line-clamp-2">{{ $company->name }}</h3>
                                        <p class="mt-1 text-xs text-[#333333]/60 line-clamp-2">{{ $company->address }}</p>

                                        @if(isset($company->distance))
                                            <p class="mt-auto pt-3 text-xs font-mono text-[#333333]/60">
                                                {{ number_format($company->distance, 1) }} km
                                            </p>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <div class="mt-8">
                            {{ $companies->links() }}
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>
</div>