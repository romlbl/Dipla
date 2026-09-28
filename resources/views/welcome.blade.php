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

        // À proximité : sélection aléatoire en attendant l'activation de la position
        // (le calcul de distance réel se fait sur /recherche?mode=nearby via la géoloc du navigateur)
        $nearbyProducts = \App\Models\Product::with(['images', 'company', 'reviews'])
            ->whereNotNull('latitude')
            ->inRandomOrder()
            ->take(8)
            ->get();

        $categories = ['Artisans', 'Fleurs', 'Librairies', 'Bijoux'];
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

        {{-- Filtres rapides (cachés sur mobile) --}}
        <div class="hidden md:flex flex-wrap justify-center gap-2">
            @foreach($categories as $category)
                <a href="{{ route('search', ['q' => $category]) }}" wire:navigate
                   class="rounded-full bg-[#4A3B5C]/10 px-4 py-2 text-xs font-semibold text-[#4A3B5C] transition hover:bg-[#4A3B5C]/20">
                    {{ $category }}
                </a>
            @endforeach
        </div>

        <p class="font-sans text-xs text-[#333333]/50">
            {{ $companiesCount }} commerces · {{ $productsCount }} produits
        </p>
        </div>
    </section>

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
</x-layouts::public>