{{--
    Carte produit utilisée dans les carrousels de la page d'accueil et la grille de recherche.

    Variables attendues :
    - $product : App\Models\Product (avec 'images', 'company', 'reviews' déjà chargés)
--}}

<div class="group relative min-w-[calc(50%-0.5rem)] w-[calc(50%-0.5rem)] sm:min-w-[240px] sm:w-[240px] shrink-0 snap-start rounded-2xl border border-[#E2E8F0] bg-[#FAFAFF] p-3 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
     x-data="{
         lat: {{ $product->latitude ?? 'null' }},
         lng: {{ $product->longitude ?? 'null' }},
         distanceLabel() {
             const loc = this.$store.searchLocation;
             if (!loc.hasLocation || this.lat === null || this.lng === null) return null;
             const R = 6371;
             const dLat = (this.lat - loc.lat) * Math.PI / 180;
             const dLng = (this.lng - loc.lng) * Math.PI / 180;
             const a = Math.sin(dLat / 2) ** 2
                 + Math.cos(loc.lat * Math.PI / 180) * Math.cos(this.lat * Math.PI / 180) * Math.sin(dLng / 2) ** 2;
             const distanceKm = R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
             return distanceKm < 1 ? `${Math.round(distanceKm * 1000)} m` : `${distanceKm.toFixed(1)} km`;
         },
     }">

    {{-- Placé en dehors du <a> ci-dessous : un clic ici ne doit jamais déclencher la navigation vers la fiche produit --}}
    <livewire:product.favorite-heart :product="$product" :key="'fav-heart-'.$product->id" />

    <a href="{{ route('products.show', $product) }}" wire:navigate.hover="false" class="block">

        {{-- Image au format portrait 2:3, cohérent avec le recadrage utilisé côté entreprise --}}
        <div class="relative w-full aspect-[2/3] overflow-hidden rounded-xl bg-[#E2E8F0]">
            @if($product->images->isNotEmpty())
                <img src="{{ $product->images->first()->url }}" alt="{{ $product->title }}"
                     class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
            @else
                <div class="flex h-full w-full items-center justify-center text-sm text-[#333333]/40">
                    Pas d'image
                </div>
            @endif

            @if($product->reviews->isNotEmpty())
                <span class="absolute bottom-2 left-2 inline-flex items-center gap-1 rounded-full bg-[#FDFBF7]/90 px-2.5 py-1 text-xs font-semibold text-[#1E293B] backdrop-blur-sm">
                    <span class="text-[#4A3B5C]">★</span> {{ $product->averageRating() }}
                </span>
            @endif
        </div>

        <div class="mt-3 space-y-0.5 px-1">
            <h3 class="truncate font-semibold text-[#1E293B]">{{ $product->title }}</h3>
            <p class="truncate text-sm text-[#333333]/70">{{ $product->company->name }}</p>
            <p class="flex items-center gap-1 text-xs text-[#333333]/60">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 text-[#4A3B5C]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="truncate">{{ $product->address }}</span>
            </p>

            <div class="mt-2 flex flex-col gap-1 border-t border-[#E2E8F0] pt-2 sm:flex-row sm:items-center sm:justify-between sm:gap-0">
                <span class="inline-block w-fit rounded-full bg-[#1E3D59] px-3 py-1 font-sans text-xs font-semibold text-[#FDFBF7]">
                    {{ $product->priceLabel() }}
                </span>
                <span x-show="distanceLabel()" x-text="distanceLabel()" x-cloak
                      class="font-sans text-xs text-[#333333]/60 whitespace-nowrap"></span>
            </div>
        </div>
    </a>
</div>