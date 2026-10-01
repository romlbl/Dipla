{{--
    Modale de sélection d'adresse pour la recherche : carte + autocomplete Nominatim.
    Écrit le résultat dans le store Alpine "searchLocation" (resources/js/location-store.js).

    Le contenu de la modale est téléporté dans <body> (x-teleport) car le header
    a un backdrop-blur : un ancêtre avec filter/backdrop-filter casse le
    positionnement "fixed" de ses descendants (ils deviennent relatifs à cet
    ancêtre au lieu du viewport), ce qui décale la carte et teinte le header.
--}}
<div x-data="locationSearchModal()" x-cloak @open-location-modal.window="openModal()">
    <button type="button" @click="openModal()" aria-label="Choisir une position de recherche"
            class="relative rounded-full p-2 text-[#1E3D59] transition hover:bg-[#E2E8F0]/60">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
    </button>

    <template x-teleport="body">
        <div x-show="open" x-transition
             class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 px-4"
             style="display: none;">
            <div @click.outside="closeModal()" class="w-full max-w-lg rounded-2xl bg-[#FDFBF7] p-6 shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-[#1E293B]">Rechercher depuis...</h2>
                    <button type="button" @click="closeModal()" aria-label="Fermer" class="text-[#333333]/50 hover:text-[#333333]">✕</button>
                </div>

                <p x-show="firstVisit" x-cloak class="mb-3 text-sm text-[#333333]/70">
                    Bienvenue ! Activez votre position ou choisissez un lieu pour voir les commerces autour de vous.
                </p>

                <div class="relative">
                    <input type="text" x-model="query" @input="onQueryInput()" @focus="focused = true" @blur="focused = false; closeSuggestions()" @keydown.escape.stop="closeSuggestions()" autocomplete="off"
                           placeholder="Ville, adresse, quartier..."
                           class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20">

                    <div x-show="suggestions.length > 0" x-cloak @mousedown.prevent
                         class="absolute z-10 mt-1 w-full rounded-xl border border-[#E2E8F0] bg-[#FAFAFF] shadow-lg max-h-60 overflow-y-auto" style="z-index: 9999;">
                        <template x-for="suggestion in suggestions" :key="suggestion.place_id">
                            <button type="button" @click="selectSuggestion(suggestion)"
                                    class="block w-full px-4 py-2 text-left text-sm text-[#333333] hover:bg-[#FDFBF7] transition"
                                    x-text="suggestion.display_name"></button>
                        </template>
                    </div>
                </div>

                <!-- resources/views/partials/location-modal.blade.php -->
                <button type="button" @click="useMyPosition()" :disabled="locating"
                        class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-[#1E3D59] hover:underline disabled:opacity-60">
                    <x-spinner x-show="locating" x-cloak />
                    <svg x-show="!locating" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span x-text="locating ? 'Localisation...' : 'Utiliser ma position actuelle'"></span>
                </button>

                <div x-ref="modalMap" class="mt-3 h-64 w-full rounded-xl border border-[#E2E8F0] overflow-hidden"></div>

                <p class="text-xs text-[#333333]/60 mt-1">Vous pouvez aussi cliquer sur la carte ou déplacer le repère.</p>

                <div class="flex gap-3 mt-5">
                    <button type="button" @click="clearLocation()"
                            class="rounded-full border border-[#E2E8F0] px-4 py-2 text-sm font-medium text-[#1E293B] transition hover:bg-white">
                        Effacer
                    </button>
                    <button type="button" @click="confirm()" :disabled="selectedLat === null"
                            class="flex-1 rounded-full bg-[#1E3D59] px-4 py-2 text-sm font-semibold text-[#FDFBF7] transition hover:bg-[#16293F] disabled:opacity-50 disabled:cursor-not-allowed">
                        Confirmer cette position
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>