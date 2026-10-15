<div x-data="{ open: false }" @keydown.escape.window="open = false">
    <button type="button" @click="open = true"
            class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-[#E2E8F0] bg-white px-5 py-2.5 text-sm font-semibold text-[#1E3D59] transition hover:bg-[#FDFBF7] sm:w-auto">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M7.5 12L12 16.5m0 0L16.5 12M12 16.5V3" />
        </svg>
        Importer un catalogue
    </button>

    <template x-teleport="body">
        <div x-show="open" x-cloak x-transition.opacity
             class="fixed inset-0 z-[100] flex items-end justify-center bg-black/40 sm:items-center sm:px-4"
             style="display: none;">
            <div class="max-h-[90vh] w-full overflow-y-auto rounded-t-2xl bg-[#FDFBF7] p-5 shadow-lg sm:max-w-lg sm:rounded-2xl sm:p-6">

                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-[#1E293B]">Importer un catalogue</h2>
                    @if(! in_array($step, ['analyzing', 'importing']))
                        <button type="button" @click="open = false; $wire.resetImport()" aria-label="Fermer"
                                class="text-[#333333]/50 hover:text-[#333333]">✕</button>
                    @endif
                </div>

                {{-- Upload --}}
                @if($step === 'upload')
                    <p class="mb-3 text-sm text-[#333333]/70">
                        Importez votre catalogue, les produits et leurs infos seront detectés automatiquement.
                    </p>

                    <label class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-[#E2E8F0] bg-white px-4 py-8 text-center transition hover:border-[#1E3D59]">
                        <span class="text-sm font-medium text-[#1E293B]">
                            {{ $file ? $file->getClientOriginalName() : 'Choisir un fichier' }}
                        </span>
                        <span class="text-xs text-[#333333]/50">10 Mo max</span>
                        <input type="file" wire:model="file" class="hidden">
                    </label>

                    <div wire:loading.flex wire:target="file" role="status" class="mt-2 flex items-center gap-2 text-sm text-[#333333]/70">
                        <x-spinner class="text-[#1E3D59]" /> Envoi du fichier…
                    </div>
                    <div wire:loading.flex wire:target="analyze" role="status" class="mt-2 items-center gap-2 text-sm text-[#333333]/70">
                        <x-spinner class="mt-0.5 shrink-0 text-[#1E3D59]" />
                        <span>Lecture en cours. Cela peut prendre quelques minutes.</span>
                    </div>
                    @error('file') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

                    <div class="mt-5 flex gap-3">
                        <button type="button" @click="open = false; $wire.resetImport()"
                                class="rounded-full border border-[#E2E8F0] px-4 py-2 text-sm font-medium text-[#1E293B] transition hover:bg-white">
                            Annuler
                        </button>
                        <button type="button" wire:click="analyze"
                                wire:loading.attr="disabled" wire:target="file,analyze"
                                @disabled(! $file)
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-full bg-[#1E3D59] px-4 py-2 text-sm font-semibold text-[#FDFBF7] transition hover:bg-[#16293F] disabled:cursor-not-allowed disabled:opacity-50">
                            <x-spinner wire:loading wire:target="analyze" />
                            Analyser
                        </button>
                    </div>
                @endif

                {{-- Analyse (placeholder, rempli aux étapes suivantes) --}}
                @if($step === 'analyzing')
                    <div role="status" class="flex flex-col items-center gap-3 py-8 text-sm text-[#333333]/70">
                        <x-spinner size="h-8 w-8" class="text-[#1E3D59]" />
                        Analyse du fichier…
                    </div>
                @endif

                {{-- 3 prévisualisations --}}
                @if($step === 'preview')
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        <span class="font-semibold">{{ $validCount }}</span> produit(s)/service(s) importable(s)
                    </div>

                    @if($rejectedCount > 0)
                        <details class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                            <summary class="cursor-pointer font-semibold">{{ $rejectedCount }} ignoré(s), non ajoutés</summary>
                            <ul class="mt-2 max-h-40 space-y-1 overflow-y-auto text-xs">
                                @foreach($rejected as $item)
                                    <li class="break-words">• {{ $item['title'] }} : {{ $item['reason'] }}</li>
                                @endforeach
                                @if($rejectedCount > count($rejected))
                                    <li>… et {{ $rejectedCount - count($rejected) }} autre(s)</li>
                                @endif
                            </ul>
                        </details>
                    @endif

                    @php
                        $allDone = ! in_array('pending', $status, true);
                    @endphp

                    <div x-data="{ cur: 0, total: {{ count($previews) }} }" class="mt-5">

                        {{-- Pagination 1/3 --}}
                        <div class="mb-2 flex items-center justify-between gap-2">
                            <button type="button" @click="cur = Math.max(0, cur - 1)" :disabled="cur === 0" aria-label="Précédent"
                                    class="flex h-9 w-9 items-center justify-center rounded-full border border-[#E2E8F0] bg-white text-lg text-[#1E293B] transition hover:bg-[#FDFBF7] disabled:opacity-30">‹</button>

                            <p class="text-sm font-semibold text-[#1E293B]">
                                Exemple <span x-text="cur + 1"></span>/<span x-text="total"></span>
                            </p>

                            <button type="button" @click="cur = Math.min(total - 1, cur + 1)" :disabled="cur === total - 1" aria-label="Suivant"
                                    class="flex h-9 w-9 items-center justify-center rounded-full border border-[#E2E8F0] bg-white text-lg text-[#1E293B] transition hover:bg-[#FDFBF7] disabled:opacity-30">›</button>
                        </div>

                        {{-- Pastilles : gris = à valider, vert = ajouté, rouge = erreur --}}
                        <div class="mb-3 flex justify-center gap-2">
                            @foreach($previews as $i => $p)
                                <button type="button" @click="cur = {{ $i }}" aria-label="Exemple {{ $i + 1 }}"
                                        :class="cur === {{ $i }} ? 'ring-2 ring-[#1E3D59] ring-offset-2 ring-offset-[#FDFBF7]' : ''"
                                        class="h-2.5 w-2.5 rounded-full {{ match($status[$i] ?? '') { 'validated' => 'bg-emerald-500', default => 'bg-[#333333]/25' } }}"></button>
                            @endforeach
                        </div>

                        {{-- Une seule carte visible à la fois --}}
                        @foreach($previews as $i => $p)
                            <div wire:key="preview-{{ $i }}" x-show="cur === {{ $i }}" x-cloak style="display: none;"
                                 class="rounded-xl border border-[#E2E8F0] bg-white p-3 text-sm sm:p-4">

                                <div class="flex gap-2 overflow-x-auto pb-1">
                                    @foreach($p['images'] as $img)
                                        <img src="{{ $img }}" alt="" loading="lazy" referrerpolicy="no-referrer"
                                             class="aspect-[2/3] w-20 shrink-0 rounded-lg border border-[#E2E8F0] bg-[#E2E8F0] object-contain sm:w-24">
                                    @endforeach
                                </div>

                                <div class="mt-3 flex items-start justify-between gap-2">
                                    <p class="break-words font-semibold text-[#1E293B]">{{ $p['title'] }}</p>
                                    <span class="shrink-0 rounded-full bg-[#1E3D59]/10 px-2.5 py-0.5 text-xs font-semibold text-[#1E3D59]">
                                        {{ $p['type'] === 'service' ? 'Service' : 'Produit' }}
                                    </span>
                                </div>

                                <p class="mt-1 font-mono text-sm font-semibold text-[#1E293B]">
                                    {{ $p['price'] !== null ? number_format($p['price'], 2).' €' : 'Prix variable' }}
                                </p>
                                <p class="mt-1 line-clamp-4 break-words text-xs text-[#333333]/70">{{ $p['description'] }}</p>

                                @if($p['keywords'])
                                    <p class="mt-1 break-words text-xs text-[#333333]/50">Mots-clés : {{ $p['keywords'] }}</p>
                                @endif

                                @if(($status[$i] ?? '') === 'pending')
                                    <button type="button"
                                            @click="$wire.confirm({{ $i }}); if (cur < total - 1) cur++"
                                            class="mt-3 w-full rounded-full bg-[#1E3D59] px-4 py-2.5 text-sm font-semibold text-[#FDFBF7] transition hover:bg-[#16293F]">
                                        Valider ces informations
                                    </button>
                                @else
                                    <p class="mt-3 text-sm font-semibold text-emerald-700">✓ Validé</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Ajouter tout : grisé tant que les exemples ne sont pas validés --}}
                    <button type="button" wire:click="addAll" @disabled(! $allDone)
                            class="mt-5 w-full rounded-full bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-40">
                        Ajouter tout ({{ $validCount }} produit{{ $validCount > 1 ? 's' : '' }})
                    </button>
                    @unless($allDone)
                        <p class="mt-1 text-center text-xs text-[#333333]/60">Validez les {{ count($previews) }} exemples pour débloquer.</p>
                    @endunless

                    <button type="button"
                            @click="const added = $wire.addedCount > 0; open = false; $wire.resetImport().then(() => added && window.location.reload())"
                            class="mt-3 w-full rounded-full border border-[#E2E8F0] bg-white px-4 py-2 text-sm font-semibold text-[#1E293B] transition hover:bg-[#FDFBF7]">
                        Fermer
                    </button>
                @endif

                {{-- Import en cours : compteur --}}
                @if($step === 'importing')
                    <div x-data="{ async run() {
                            try { while (await $wire.processBatch()) {} }
                            catch (e) { $wire.failImport() }
                         } }"
                         x-init="run()" role="status" aria-live="polite"
                         class="flex flex-col items-center gap-4 py-6 text-center">
                        <x-spinner size="h-8 w-8" class="text-[#1E3D59]" />

                        <p class="font-mono text-2xl font-semibold text-[#1E293B]">{{ $cursor }}/{{ $validCount }}</p>

                        <div class="h-2 w-full overflow-hidden rounded-full bg-[#E2E8F0]">
                            <div class="h-full bg-[#1E3D59] transition-all duration-300"
                                 style="width: {{ $validCount > 0 ? round($cursor / $validCount * 100) : 0 }}%"></div>
                        </div>

                        <p class="text-sm text-[#333333]/70">Ajout en cours. Ne fermez pas cette page.</p>
                    </div>
                @endif

                {{-- Erreur / succès --}}
                @if(in_array($step, ['done', 'error']))
                    <p class="rounded-xl border px-4 py-3 text-sm {{ $step === 'done' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700' }}">
                        {{ $message }}
                    </p>
                    <button type="button" @click="const added = $wire.addedCount > 0; open = false; $wire.resetImport().then(() => added && window.location.reload())"
                            class="mt-5 w-full rounded-full bg-[#1E3D59] px-4 py-2 text-sm font-semibold text-[#FDFBF7] transition hover:bg-[#16293F]">
                        Fermer
                    </button>
                @endif
            </div>
        </div>
    </template>
</div>