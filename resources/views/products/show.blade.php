<x-layouts::public>
    <div class="max-w-6xl mx-auto" x-data="{ tab: 'description' }">

        {{-- ============================= --}}
        {{-- Hero : galerie + infos produit --}}
        {{-- ============================= --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-8 mb-12">

            {{-- Colonne gauche : image principale + miniatures --}}
            {{--
                Mobile  : image en haut, miniatures en dessous, côte à côte
                Tablette (md) : même grille 2 colonnes que desktop, miniatures encore en dessous de l'image
                Desktop (lg+) : miniatures empilées à gauche, image à droite
            --}}
            <div class="md:col-span-2">
                <div class="flex flex-col lg:flex-row gap-3">

                    {{-- Miniatures --}}
                    @if($product->images->count() > 1)
                        <div class="order-2 lg:order-1 flex flex-row lg:flex-col gap-2 overflow-x-auto lg:overflow-y-auto lg:max-h-[380px] lg:w-20 shrink-0 pb-1 lg:pb-0">
                            @foreach($product->images as $image)
                                <button type="button"
                                        onclick="document.getElementById('main-product-image').src = '{{ $image->url }}'"
                                        class="w-16 lg:w-full shrink-0 aspect-[2/3] overflow-hidden rounded-lg border-2 border-[#E2E8F0] opacity-70 hover:opacity-100 hover:border-[#1E3D59] transition">
                                    <img src="{{ $image->url }}" alt="" class="h-full w-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- Image principale --}}
                    <div class="order-1 lg:order-2 relative flex-1 max-w-[260px] mx-auto md:mx-0 aspect-[2/3] overflow-hidden rounded-2xl border border-[#E2E8F0] bg-[#E2E8F0]">
                        @if($product->images->isNotEmpty())
                            <img id="main-product-image"
                                 src="{{ $product->images->first()->url }}"
                                 alt="{{ $product->title }}"
                                 class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-sm text-[#333333]/40">
                                Pas d'image
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Colonne droite : titre, prix, actions, commerce --}}
            <div class="md:col-span-3 flex flex-col">
                {{-- Titre + note, sur la même ligne, chacun à un bout --}}
                <div class="flex items-start justify-between gap-3 mb-1">
                    <h1 class="text-xl md:text-2xl font-bold text-[#1E293B] leading-tight">
                        {{ $product->title }}
                    </h1> 

                    @if($product->reviews->isNotEmpty())
                        <button type="button" 
                        @click="
                        tab = 'avis'
                        $nextTick(() => {
                            document.getElementById('interaction-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        })
                        "
                                class="shrink-0 flex items-center gap-1 text-sm hover:underline">
                            <span class="text-amber-500">★</span>
                            <span class="font-medium text-[#1E293B]">{{ $product->averageRating() }}</span>
                            <span class="text-[#333333]/60">({{ $product->reviews->count() }})</span>
                        </button>
                    @endif
                </div>

                @if($product->reviews->isEmpty())
                    <p class="text-sm text-[#333333]/50 mb-3">Aucun avis pour l'instant</p>
                @else
                    <div class="mb-3"></div>
                @endif
                <p class="flex items-start gap-1 text-sm text-[#333333]/50 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 text-[#4A3B5C] mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>{{ $product->address }}</span>
                </p>

                {{-- Prix, sans fond ni bordure --}}
                <div class="mb-5">
                    <span class="inline-block w-fit rounded-full bg-[#1E3D59] px-3 py-1 font-sans text-sm font-semibold text-[#FDFBF7]">
                        {{ $product->priceLabel() }}
                    </span>
                </div>

                {{-- Actions : Contacter puis Favoris en dessous --}}
                <div class="flex flex-col gap-3 mb-5">
                    <button type="button"
                            @click="
                                tab = 'questions';
                                $nextTick(() => {
                                    document.getElementById('question-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                    document.getElementById('new-question-input')?.focus();
                                });
                            "
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#1E3D59] px-6 py-3 text-sm font-semibold text-[#FDFBF7] transition hover:bg-[#16293F]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zM21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                        </svg>
                        Contacter le commerce
                    </button>
                    <p class="mt-5 text-xs text-[#333333]/50">
                        Dernière mise à jour le {{ $product->updated_at->timezone('Europe/Paris')->format('d/m/Y') }}
                    </p>

                    <livewire:product.favorite-button :product="$product" />
                </div>

                {{-- Carte commerce, avec la disponibilité en dessous des infos, dans le même bloc --}}
                <a href="{{ route('company.storefront', $product->company) }}" wire:navigate
                   class="flex flex-col gap-2 rounded-xl border border-[#E2E8F0] bg-[#FAFAFF] p-3 transition hover:border-[#1E3D59] group">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                                @php($avatarImage = $product->company->avatar_image_url ?? $product->company->cover_image_url)
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#E2E8F0] overflow-hidden">
                                    @if($avatarImage)
                                        <img src="{{ $avatarImage }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        <span class="text-[#1E3D59] font-semibold text-sm">{{ mb_substr($product->company->name, 0, 1) }}</span>
                                    @endif
                                </div>
                            <div class="min-w-0">
                                <h3 class="font-medium text-[#1E293B] text-sm truncate group-hover:text-[#1E3D59] transition">
                                    {{ $product->company->name }}
                                </h3>
                                <p class="text-xs text-[#333333]/60 truncate">{{ $product->address }}</p>
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-[#333333]/40 group-hover:text-[#1E3D59] transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </div>

                    @if($product->company->isOpenNow() === true)
                        <p class="flex items-center gap-1.5 text-sm text-emerald-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Actuellement ouvert
                        </p>
                    @elseif($product->company->isOpenNow() === false)
                        <p class="flex items-center gap-1.5 text-sm text-red-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Actuellement fermé
                        </p>
                    @endif
                </a>
            </div>
        </div>

        {{-- ============================= --}}
        {{-- Onglets : Description / Avis / Questions --}}
        {{-- ============================= --}}
        <section id="interaction-section" class="mb-12">
            <div class="flex gap-6 border-b border-[#E2E8F0] mb-6 overflow-x-auto">
                <button @click="tab = 'description'"
                        :class="tab === 'description' ? 'border-[#1E3D59] text-[#1E3D59]' : 'border-transparent text-[#333333]/60 hover:text-[#1E293B]'"
                        class="pb-3 border-b-2 text-sm font-semibold whitespace-nowrap transition">
                    Description
                </button>
                <button @click="tab = 'avis'"
                        :class="tab === 'avis' ? 'border-[#1E3D59] text-[#1E3D59]' : 'border-transparent text-[#333333]/60 hover:text-[#1E293B]'"
                        class="pb-3 border-b-2 text-sm font-semibold whitespace-nowrap transition">
                    Avis ({{ $product->reviews->count() }})
                </button>
                <button @click="tab = 'questions'"
                        :class="tab === 'questions' ? 'border-[#1E3D59] text-[#1E3D59]' : 'border-transparent text-[#333333]/60 hover:text-[#1E293B]'"
                        class="pb-3 border-b-2 text-sm font-semibold whitespace-nowrap transition">
                    Questions ({{ $product->discussions->count() }})
                </button>
            </div>

            {{-- Description --}}
            <div x-show="tab === 'description'" class="max-w-3xl">
                <x-rich-text :text="$product->description" class="text-[#333333]/80 leading-relaxed" />

                @if($product->keywords)
                    <div class="flex flex-wrap gap-2 mt-5">
                        @foreach(explode(',', $product->keywords) as $keyword)
                            <span class="inline-flex items-center rounded-full border border-[#E2E8F0] px-3 py-1 text-xs text-[#333333]">
                                {{ trim($keyword) }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Avis --}}
            <div x-show="tab === 'avis'" x-cloak>
                <livewire:product.reviews-section :product="$product" />
            </div>

            {{-- Questions --}}
            <div id="question-section" x-show="tab === 'questions'" x-cloak>
                <livewire:product.discussions :product="$product" />
            </div>
        </section>

        {{-- ============================= --}}
        {{-- Itinéraire --}}
        {{-- ============================= --}}
        <section class="mb-12">
            <h2 class="text-lg font-semibold text-[#1E293B] mb-4">Venir chercher ce produit</h2>

            @if($product->latitude && $product->longitude)
                <div x-data="routePreview({{ $product->latitude }}, {{ $product->longitude }})"
                     class="rounded-2xl border border-[#E2E8F0] bg-[#FAFAFF] p-4 shadow-sm">

                    {{-- Modes de transport --}}
                    <div class="flex gap-2 mb-3 overflow-x-auto">
                        <button type="button" @click="setMode('walking')"
                                :class="mode === 'walking' ? 'bg-[#1E3D59] text-[#FDFBF7]' : 'border border-[#E2E8F0] text-[#333333] hover:bg-white'"
                                class="shrink-0 inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium transition">
                            À pied
                            <span x-show="durations.walking" x-text="durations.walking" class="font-sans text-xs opacity-80"></span>
                        </button>

                        <button type="button" @click="setMode('driving')"
                                :class="mode === 'driving' ? 'bg-[#1E3D59] text-[#FDFBF7]' : 'border border-[#E2E8F0] text-[#333333] hover:bg-white'"
                                class="shrink-0 inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium transition">
                            Voiture
                            <span x-show="durations.driving" x-text="durations.driving" class="font-sans text-xs opacity-80"></span>
                        </button>
                    </div>

                    <div x-ref="map" class="h-64 w-full rounded-xl border border-[#E2E8F0] overflow-hidden"></div>

                    <p class="text-sm text-[#333333] mt-3">
                        <span x-show="loading" x-cloak class="inline-flex items-center gap-2">
                            <x-spinner class="text-[#1E3D59]" /> Calcul de l'itinéraire…
                        </span>
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

        {{-- ============================= --}}
        {{-- Produits similaires --}}
        {{-- ============================= --}}
        @if($related->isNotEmpty())
            <section class="mb-12">
                <h2 class="text-lg font-semibold text-[#1E293B] mb-4">Vous aimerez aussi</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    @foreach($related as $relatedProduct)
                        <a href="{{ route('products.show', $relatedProduct) }}" wire:navigate
                           class="group flex flex-col overflow-hidden rounded-xl border border-[#E2E8F0] bg-[#FAFAFF] shadow-sm hover:shadow-md transition">
                            <div class="aspect-[2/3] w-full bg-[#E2E8F0] overflow-hidden">
                                @if($relatedProduct->images->isNotEmpty())
                                    <img src="{{ $relatedProduct->images->first()->url }}" alt=""
                                         class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-sm text-[#333333]/40">
                                        Pas d'image
                                    </div>
                                @endif
                            </div>
                            <div class="p-3">
                                <p class="text-sm font-medium text-[#1E293B] truncate">{{ $relatedProduct->title }}</p>
                                <p class="text-xs font-sans text-[#333333]/70 mt-1">{{ $relatedProduct->priceLabel() }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

    </div>
</x-layouts::public>
