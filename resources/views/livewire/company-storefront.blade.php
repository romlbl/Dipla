@php
    $days = [
        'mon' => 'Lundi',
        'tue' => 'Mardi',
        'wed' => 'Mercredi',
        'thu' => 'Jeudi',
        'fri' => 'Vendredi',
        'sat' => 'Samedi',
        'sun' => 'Dimanche',
    ];
    $isOpen = $company->isOpenNow();
    $reviewsCount = $company->reviews()->count();
    $avgRating = $company->averageRating();
@endphp

<div class="flex flex-col gap-8 md:gap-10">

    {{-- ============================================================= --}}
    {{-- 1. Hero : image de l'entreprise                                --}}
    {{-- ============================================================= --}}

    {{-- Version bannière (tablette / desktop) --}}
    <section class="hidden sm:flex relative w-full h-[220px] md:h-[400px] rounded-2xl overflow-hidden shadow-sm flex-col justify-end
                {{ $company->cover_image_url ? '' : 'bg-gradient-to-br from-[#1E3D59] to-[#0F2438]' }}">
        @if($company->cover_image_url)
            <img src="{{ $company->cover_image_url }}" alt="{{ $company->name }}"
                 class="absolute inset-0 h-full w-full object-contain md:object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
        @else
            <div class="absolute inset-0 opacity-10">
                <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
                    <defs>
                        <pattern id="storefront-hero-grid" width="32" height="32" patternUnits="userSpaceOnUse">
                            <path d="M32 0H0V32" fill="none" stroke="white" stroke-width="1"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#storefront-hero-grid)" />
                </svg>
            </div>
        @endif

        <div class="relative p-5 md:p-8" wire:ignore x-data="fitHeroText">
            <h1 class="text-2xl md:text-4xl font-extrabold text-white mb-1">{{ $company->name }}</h1>
            @if($company->description)
                <x-rich-text x-ref="desc" :text="$company->description" class="text-sm md:text-base text-white/80 max-w-2xl" />
            @endif
        </div>
    </section>

    {{-- Version mobile : visuel carte au format 2:3 --}}
    @php($mobileImage = $company->card_image_url ?? $company->cover_image_url)
    <section class="sm:hidden relative w-full aspect-[2/3] max-w-xs mx-auto rounded-2xl overflow-hidden shadow-sm flex flex-col justify-end
                {{ $mobileImage ? '' : 'bg-gradient-to-br from-[#1E3D59] to-[#0F2438]' }}">
        @if($mobileImage)
            <img src="{{ $mobileImage }}" alt="{{ $company->name }}"
                 class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
        @else
            <div class="absolute inset-0 opacity-10">
                <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
                    <defs>
                        <pattern id="storefront-hero-grid-mobile" width="32" height="32" patternUnits="userSpaceOnUse">
                            <path d="M32 0H0V32" fill="none" stroke="white" stroke-width="1"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#storefront-hero-grid-mobile)" />
                </svg>
            </div>
        @endif

        <div class="relative p-4" wire:ignore x-data="fitHeroText">
            <h1 class="text-xl font-extrabold text-white mb-1">{{ $company->name }}</h1>
            @if($company->description)
                <x-rich-text x-ref="desc" :text="$company->description" class="text-xs text-white/80" />
            @endif
        </div>
    </section>

    {{-- ============================================= --}}
    {{-- 2. Bloc infos pratiques (horaires + contact)    --}}
    {{-- ordre inversé : les infos passent AVANT la recherche produits --}}
    {{-- ============================================= --}}
    <section class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">

        {{-- Horaires --}}
        <div class="rounded-2xl border border-[#E2E8F0] bg-[#FAFAFF] p-5 md:p-6 shadow-sm flex flex-col gap-4">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#E2E8F0] pb-3">
                <h2 class="text-base md:text-lg font-semibold text-[#1E293B] flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Horaires
                </h2>

                @if($isOpen !== null)
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold
                                 {{ $isOpen ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-700' }}">
                        <span class="h-2 w-2 rounded-full {{ $isOpen ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                        {{ $isOpen ? 'Ouvert actuellement' : 'Actuellement fermé' }}
                    </span>
                @endif
            </div>

            @if($company->opening_hours)
                <ul class="flex flex-col gap-2 text-sm text-[#333333]">
                    @foreach($days as $key => $label)
                        @php($day = $company->opening_hours[$key] ?? null)
                        <li class="flex justify-between gap-3">
                            <span>{{ $label }}</span>
                            @if(!$day || ($day['closed'] ?? true))
                                <span class="text-[#333333]/50">Fermé</span>
                            @elseif(($day['has_break'] ?? false) && ($day['break_start'] ?? null) && ($day['break_end'] ?? null))
                                <span class="text-right font-medium text-[#1E293B]">
                                    {{ $day['open'] }} - {{ $day['break_start'] }} · {{ $day['break_end'] }} - {{ $day['close'] }}
                                </span>
                            @else
                                <span class="font-medium text-[#1E293B]">{{ $day['open'] }} - {{ $day['close'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-[#333333]/60">Horaires non renseignés.</p>
            @endif
        </div>

        {{-- Contact --}}
        <div class="rounded-2xl border border-[#E2E8F0] bg-[#FAFAFF] p-5 md:p-6 shadow-sm flex flex-col gap-4">
            <h2 class="text-base md:text-lg font-semibold text-[#1E293B] flex items-center gap-2 border-b border-[#E2E8F0] pb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                Informations pratiques
            </h2>

            <div class="flex flex-col gap-4">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#1E3D59]/10 text-[#1E3D59]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-[#1E293B]">Adresse</h3>
                        <p class="text-sm text-[#333333]/70 mt-0.5">{{ $company->address }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#1E3D59]/10 text-[#1E3D59]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-[#1E293B]">Email</h3>
                        <p class="text-sm text-[#333333]/70 mt-0.5">{{ $company->email }}</p>
                    </div>
                </div>
                
                @if($company->phone)
                    <div class="flex items-start gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#1E3D59]/10 text-[#1E3D59]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-[#1E293B]">Téléphone</h3>
                            <a href="tel:{{ $company->phone }}" class="text-sm text-[#333333]/70 mt-0.5 hover:text-[#1E3D59] transition">
                                {{ $company->phone }}
                            </a>
                        </div>
                    </div>
                @endif

                @if($company->website)
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#1E3D59]/10 text-[#1E3D59]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18zm0 0c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3M3.6 9h16.8M3.6 15h16.8" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-medium text-[#1E293B]">Site web</h3>
                        <a href="{{ $company->website }}" target="_blank" rel="noopener noreferrer nofollow"
                        class="mt-0.5 block break-all text-sm text-[#333333]/70 transition hover:text-[#1E3D59]">
                            {{ preg_replace('#^www\.#i', '', parse_url($company->website, PHP_URL_HOST) ?: $company->website) }}
                        </a>
                    </div>
                </div>
            @endif

                @if($reviewsCount > 0)
                    <div class="flex items-start gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#1E3D59]/10 text-amber-500 leading-none text-lg">★</div>
                        <div>
                            <h3 class="text-sm font-medium text-[#1E293B]">Note</h3>
                            <p class="text-sm text-[#333333]/70 mt-0.5">{{ $avgRating }}/5 · {{ $reviewsCount }} avis</p>
                        </div>
                    </div>
                @endif
                <p class="text-xs text-[#333333]/50">
                    Dernière mise à jour le {{ $company->updated_at->timezone('Europe/Paris')->format('d/m/Y') }}
                </p>
            </div>
        </div>
    </section>

    {{-- ============================================= --}}
    {{-- 3. Recherche + catalogue produits (product-card réutilisée) --}}
    {{-- ============================================= --}}
    <section class="flex flex-col gap-5">
        <div class="relative w-full shadow-sm rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#333333]/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
            </svg>
            <input type="text" wire:model.live.debounce.400ms="search"
                   placeholder="Rechercher un produit chez {{ $company->name }}..."
                   class="w-full h-14 md:h-16 pl-14 pr-5 rounded-full border border-[#E2E8F0] bg-[#FAFAFF] text-sm md:text-base text-[#333333] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20 focus:border-[#1E3D59] transition">
        </div>

        @if($products->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#E2E8F0] p-10 text-center">
                <p class="text-[#333333]">Aucun produit ne correspond à cette recherche.</p>
            </div>
        @else
            <div class="flex flex-wrap gap-4 sm:gap-6">
                @foreach($products as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>

            <div class="mt-4">
                {{ $products->links() }}
            </div>
        @endif
    </section>

    {{-- ============================================= --}}
    {{-- 4. Carte & itinéraire (réutilise routePreview + coords existantes) --}}
    {{-- ============================================= --}}
    <section class="flex flex-col gap-4">
        <h2 class="text-lg font-semibold text-[#1E293B]">Venir en boutique</h2>

        @if($company->latitude && $company->longitude)
            <div wire:ignore
                x-data="routePreview({{ $company->latitude }}, {{ $company->longitude }})"
                class="rounded-2xl border border-[#E2E8F0] bg-[#FAFAFF] p-4 shadow-sm">

                <div class="flex gap-2 mb-3 overflow-x-auto">
                    <button type="button" @click="setMode('walking')"
                            :class="mode === 'walking' ? 'bg-[#1E3D59] text-[#FDFBF7]' : 'border border-[#E2E8F0] text-[#333333] hover:bg-white'"
                            class="shrink-0 inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium transition">
                        À pied
                        <span x-show="durations.walking" x-text="durations.walking" class="font-mono text-xs opacity-80"></span>
                    </button>
                    <button type="button" @click="setMode('cycling')"
                            :class="mode === 'cycling' ? 'bg-[#1E3D59] text-[#FDFBF7]' : 'border border-[#E2E8F0] text-[#333333] hover:bg-white'"
                            class="shrink-0 inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium transition">
                        Vélo
                        <span x-show="durations.cycling" x-text="durations.cycling" class="font-mono text-xs opacity-80"></span>
                    </button>
                    <button type="button" @click="setMode('driving')"
                            :class="mode === 'driving' ? 'bg-[#1E3D59] text-[#FDFBF7]' : 'border border-[#E2E8F0] text-[#333333] hover:bg-white'"
                            class="shrink-0 inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium transition">
                        Voiture
                        <span x-show="durations.driving" x-text="durations.driving" class="font-mono text-xs opacity-80"></span>
                    </button>
                </div>

                                <div x-ref="map" class="isolate h-64 md:h-80 w-full rounded-xl border border-[#E2E8F0] overflow-hidden"></div>

                <p class="text-sm text-[#333333] mt-3">
                    <span x-show="loading">Calcul de l'itinéraire...</span>
                    <span x-show="!loading && error" x-text="error" class="text-[#4A3B5C]"></span>
                    <template x-if="!loading && !error && distanceKm !== null">
                        <span>
                            <span x-text="distanceKm.toFixed(1)"></span> km · environ
                            <span x-text="durations[mode]"></span>
                            <span x-text="mode === 'walking' ? 'à pied' : (mode === 'cycling' ? 'à vélo' : 'en voiture')"></span>
                        </span>
                    </template>
                </p>
            </div>
        @else
            <p class="text-sm text-[#333333]/60">Position non renseignée pour ce commerce.</p>
        @endif
    </section>

</div>