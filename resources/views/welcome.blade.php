@push('body-top')
    {{-- Fond Neat : fixe, couvre tout le viewport en permanence (donc aussi visible
         au niveau du footer une fois scrollé), uniquement sur la page d'accueil. --}}
    <canvas id="neat-home-background"
            class="fixed inset-0 z-0 h-screen w-screen"
            style="pointer-events: none;"
            aria-hidden="true"></canvas>
@endpush

<x-layouts::public>
    @php
        $companiesCount = \App\Models\Company::count();
        $productsCount = \App\Models\Product::count();


        // Commerces du jour
        $commerces = \App\Models\Company::with(['reviews'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->take(8)
            ->get();

        // Nouveau : les publications les plus récentes
        $newProducts = \App\Models\Product::with(['images', 'company', 'reviews'])
            ->latest()
            ->take(8)
            ->get();

        // Recommandation : les produits les plus mis en favoris
        $recommendedProducts = \App\Models\Product::withCount('favoritedBy')
            ->with(['images', 'company', 'reviews'])
            ->orderByDesc('favorited_by_count')
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        // À proximité : tri réel pour les utilisateurs dont la position est connue.
        $user = auth()->user();
        $nearbyQuery = \App\Models\Product::with(['images', 'company', 'reviews']);

        if ($user?->latitude !== null && $user?->longitude !== null) {
            $nearbyProducts = $nearbyQuery
                ->nearby((float) $user->latitude, (float) $user->longitude)
                ->orderBy('distance')
                ->take(8)
                ->get();
        } else {
            // Sans position, le navigateur doit d'abord être autorisé à la partager.
            $nearbyProducts = $nearbyQuery
                ->whereNotNull('latitude')
                ->inRandomOrder()
                ->take(8)
                ->get();
        }

    @endphp

    {{-- Hero --}}
    <section class="wave-hero relative flex flex-col items-center gap-6 py-2 text-center md:py-5">


        <div class="relative z-10 flex w-full flex-col items-center gap-6">
        <h1 class="max-w-3xl text-2xl font-extrabold tracking-tight text-[#1E293B] md:text-4xl">
            Trouvez ce qu'il vous faut, près de chez vous.
        </h1>
        <p class="hidden max-w-xl text-base text-[#333333]/80 md:block md:text-lg">
            Découvrez les meilleurs artisans, commerces et produits locaux autour de vous en un instant.
        </p>

        {{-- Barre de recherche --}}
        <form action="{{ route('search') }}" method="GET" class="mt-2 w-full max-w-2xl">
            <div class="flex flex-col items-stretch gap-2 rounded-3xl border border-[#E2E8F0] bg-[#FAFAFF] p-2 shadow-sm transition-colors focus-within:border-[#1E3D59] md:flex-row md:items-center">
                <div class="flex flex-1 items-center gap-2 px-3 py-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-[#333333]/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                    <input type="text" name="q" placeholder="Que recherchez-vous ?"
                           class="w-full border-0 bg-transparent p-0 text-sm text-[#333333] placeholder-[#333333]/40 focus:outline-none focus:ring-0">
                </div>


                <button type="button" x-data
                        @click="window.dispatchEvent(new CustomEvent('open-location-modal'))"
                        class="mx-1 inline-flex shrink-0 items-center justify-center gap-1.5 whitespace-nowrap rounded-full bg-[#FDFBF7] px-4 py-2.5 text-sm font-medium text-[#1E3D59] transition hover:bg-[#E2E8F0] my-2 md:my-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span x-text="$store.searchLocation.hasLocation ? $store.searchLocation.label : 'Ma ville'"
                        class="max-w-[9rem] truncate">Ma ville</span>
                </button>
        

                <button type="submit"
                        class="shrink-0 rounded-full bg-[#1E3D59] px-6 py-2.5 text-sm font-semibold text-[#FDFBF7] transition hover:bg-[#16293F]">
                    Rechercher
                </button>
            </div>
        </form>

        <p class="font-sans text-xs text-[#333333]/50">
            {{ $companiesCount }} commerces · {{ $productsCount }} produits
        </p>
        </div>
    </section>

    {{-- Commerce --}}
    @if($commerces->isNotEmpty())
        <section class="space-y-4 py-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-[#1E293B] md:text-2xl">Commerces du jour</h2>
            </div>
            <div class="hide-scrollbar flex snap-x gap-4 overflow-x-auto pb-2">
                @foreach($commerces as $commerce)
                    <a href="{{ route('company.storefront', $commerce) }}" wire:navigate
                        class="group flex w-[calc(50%-0.5rem)] min-w-[calc(50%-0.5rem)] shrink-0 snap-start flex-col overflow-hidden rounded-2xl border border-[#E2E8F0] bg-[#FAFAFF] shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md sm:w-[240px] sm:min-w-[240px]">
                        <div class="relative aspect-[2/3] w-full bg-[#E2E8F0]">
                            @php($cardImage = $commerce->card_image_url ?? $commerce->cover_image_url)
                            @if($cardImage)
                                <img src="{{ $cardImage }}" alt="{{ $commerce->name }}"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-sm text-[#333333]/40">
                                    Pas d'image
                                </div>
                            @endif
                            @if($commerce->reviews_count > 0)
                                <span class="absolute bottom-2 left-2 inline-flex items-center gap-1 rounded-full bg-[#FDFBF7]/90 px-2.5 py-1 text-xs font-semibold text-[#1E293B] backdrop-blur-sm">
                                    <span class="text-[#4A3B5C]">★</span> {{ number_format($commerce->reviews_avg_rating, 1) }}
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-1 flex-col p-4">
                            <h3 class="font-semibold text-[#1E293B] line-clamp-2">{{ $commerce->name }}</h3>
                            <p class="mt-1 text-xs text-[#333333]/60 line-clamp-2">{{ $commerce->address }}</p>

                            @if(isset($commerce->distance))
                                <p class="mt-auto pt-3 text-xs font-mono text-[#333333]/60">
                                    {{ number_format($commerce->distance, 1) }} km
                                </p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- À proximité --}}
    @if($nearbyProducts->isNotEmpty())
        <section class="space-y-4 border-t border-[#E2E8F0] py-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-[#1E293B] md:text-2xl">À proximité</h2>
                <a href="{{ route('search', ['mode' => 'nearby']) }}" wire:navigate class="font-sans text-xs text-[#4A3B5C] hover:underline">
                    Activer ma position
                </a>
            </div>
            <div class="hide-scrollbar flex snap-x gap-4 overflow-x-auto pb-2">
                @foreach($nearbyProducts as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif

    {{-- Recommandation --}}
    @if($recommendedProducts->isNotEmpty())
        <section class="space-y-4 border-t border-[#E2E8F0] py-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-[#1E293B] md:text-2xl">Recommandation</h2>
            </div>
            <div class="hide-scrollbar flex snap-x gap-4 overflow-x-auto pb-2">
                @foreach($recommendedProducts as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif

    {{-- Nouveau --}}
    @if($newProducts->isNotEmpty())
        <section class="space-y-4 py-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-[#1E293B] md:text-2xl">Nouveau</h2>
                <a href="{{ route('search') }}" wire:navigate class="font-sans text-xs text-[#4A3B5C] hover:underline">
                    Tout voir
                </a>
            </div>
            <div class="hide-scrollbar flex snap-x gap-4 overflow-x-auto pb-2">
                @foreach($newProducts as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif

</x-layouts::public>