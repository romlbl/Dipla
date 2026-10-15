<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-[#1E293B] flex items-center gap-2">
            Questions Clients
            @if($stats['pending_count'] > 0)
                <span class="rounded-full bg-red-500 px-2.5 py-0.5 text-xs font-bold text-white">
                    {{ $stats['pending_count'] }} en attente
                </span>
            @endif
        </h1>
        <p class="text-sm text-[#333333]/70 mt-1">Répondez rapidement pour maximiser vos ventes.</p>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="relative overflow-hidden rounded-xl border border-[#E2E8F0] bg-[#FAFAFF] p-5 shadow-sm flex flex-col justify-between h-32">
            <div class="flex items-center justify-between">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z" />
                </svg>
            </div>
            <div>
                <p class="text-2xl font-mono font-bold text-[#1E293B]">{{ $stats['pending_count'] }} question{{ $stats['pending_count'] > 1 ? 's' : '' }}</p>
                <p class="text-sm text-[#333333]/60">en attente de réponse</p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-xl border border-[#E2E8F0] bg-[#FAFAFF] p-5 shadow-sm flex flex-col justify-between h-32">
            <div class="flex items-center justify-between">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M12 21a9 9 0 100-18 9 9 0 000 18z" />
                </svg>
            </div>
            <div>
                <p class="text-2xl font-mono font-bold text-[#1E293B]">{{ $stats['avg_response_label'] }}</p>
                <p class="text-sm text-[#333333]/60">temps moyen de réponse</p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-xl border border-[#E2E8F0] bg-[#FAFAFF] p-5 shadow-sm flex flex-col justify-between h-32">
            <div class="flex items-center justify-between">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-[#1E3D59]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                </svg>
            </div>
            <div>
                <p class="text-2xl font-mono font-bold text-[#1E293B]">{{ $stats['total_count'] }} question{{ $stats['total_count'] > 1 ? 's' : '' }}</p>
                <p class="text-sm text-[#333333]/60">posées au total</p>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="flex flex-col md:flex-row gap-3 mb-6 p-4 rounded-xl border border-[#E2E8F0] bg-[#FAFAFF] shadow-sm">
        <div class="relative flex-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#333333]/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
            </svg>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Rechercher une question, un produit..."
                   class="w-full rounded-full border border-[#E2E8F0] bg-white pl-11 pr-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/10">
        </div>

        <div class="flex gap-3 overflow-x-auto">
            <select wire:model.live="productId"
                    class="rounded-full border border-[#E2E8F0] bg-white px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none min-w-[150px]">
                <option value="">Tous les produits</option>
                @foreach($products as $id => $title)
                    <option value="{{ $id }}">{{ $title }}</option>
                @endforeach
            </select>

            <select wire:model.live="status"
                    class="rounded-full border border-[#E2E8F0] bg-white px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none min-w-[150px]">
                <option value="">Toutes</option>
                <option value="pending">En attente</option>
                <option value="answered">Répondues</option>
            </select>

            <select wire:model.live="sort"
                    class="rounded-full border border-[#E2E8F0] bg-white px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none min-w-[150px]">
                <option value="recent">Plus récentes</option>
                <option value="oldest">Plus anciennes</option>
            </select>
        </div>
    </div>

    {{-- Liste des questions --}}
    @if($questions->isEmpty())
        <div class="rounded-2xl border border-dashed border-[#E2E8F0] p-10 text-center">
            <p class="text-[#333333]">Aucune question ne correspond à ces filtres.</p>
        </div>
    @else
        <div class="flex flex-col gap-5">
            @foreach($questions as $question)
                <div wire:key="question-{{ $question->id }}"
                     class="rounded-xl border border-[#E2E8F0] bg-[#FAFAFF] p-5 md:p-6 shadow-sm {{ !$question->companyReply ? 'border-l-4 border-l-red-400' : '' }}">
                    <div class="flex flex-col md:flex-row gap-5">
                        {{-- Produit --}}
                        <div class="w-full md:w-40 shrink-0 flex items-center gap-3 md:flex-col md:items-start">
                            <div class="w-16 h-16 md:w-full md:h-auto md:aspect-[2/3] shrink-0 rounded-lg overflow-hidden border border-[#E2E8F0] bg-[#E2E8F0]">
                                @if($question->product->images->isNotEmpty())
                                    <img src="{{ $question->product->images->first()->url }}" alt=""
                                         class="h-full w-full object-cover">
                                @endif
                            </div>
                            <h3 class="font-medium text-[#1E293B] text-sm">{{ $question->product->title }}</h3>
                        </div>

                        {{-- Contenu --}}
                        <div class="flex-1 flex flex-col">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div>
                                    <p class="text-sm font-medium text-[#1E293B]">{{ $question->user->name ?? 'Utilisateur' }}</p>
                                    <p class="text-xs text-[#333333]/50">{{ $question->created_at->diffForHumans() }}</p>
                                </div>

                                @if(!$question->companyReply)
                                    <span class="shrink-0 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                        En attente
                                    </span>
                                @else
                                    <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                        Répondue
                                    </span>
                                @endif
                            </div>

                            <p class="text-sm text-[#333333] font-medium">{{ $question->content }}</p>

                            {{-- Réponse existante --}}
                            @php($reply = $question->companyReply)

                            @if($reply && $editingReplyId !== $question->id)
                                <div class="mt-4 rounded-lg border-l-4 border-[#1E3D59] bg-white p-3">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="text-xs font-semibold text-[#1E3D59]">
                                            {{ $reply->user?->name ?? $question->product->company->name }}
                                            @if(!$reply->user)
                                                <span class="font-normal text-[#4A3B5C]">· Réponse du commerce</span>
                                            @endif
                                            <span class="font-normal text-[#333333]/50">· {{ $reply->created_at->diffForHumans() }}</span>
                                        </p>
                                        <button type="button" wire:click="startReply({{ $question->id }}, @js($reply->content))"
                                                class="text-xs font-medium text-[#1E3D59] hover:underline">
                                            Modifier
                                        </button>
                                    </div>
                                    <p class="text-sm text-[#333333]/80">{{ $reply->content }}</p>
                                </div>
                            @endif

                            {{-- Formulaire de réponse (nouvelle ou édition) --}}
                            @if(!$question->companyReply || $editingReplyId === $question->id)
                                <div class="mt-4 border-t border-[#E2E8F0] pt-4">
                                    <textarea wire:model="replyContent.{{ $question->id }}" rows="2"
                                              placeholder="Écrire une réponse publique..."
                                              class="w-full rounded-lg border border-[#E2E8F0] bg-white px-3 py-2 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/10 resize-none"></textarea>
                                    @error("replyContent.{$question->id}") <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                    <div class="flex justify-end gap-2 mt-2">
                                        @if($editingReplyId === $question->id)
                                            <button type="button" wire:click="cancelReply"
                                                    class="rounded-full border border-[#E2E8F0] px-4 py-1.5 text-xs font-medium text-[#1E293B] hover:bg-[#FDFBF7] transition">
                                                Annuler
                                            </button>
                                        @endif
                                    <button type="button" wire:click="submitReply({{ $question->id }})"
                                        wire:loading.attr="disabled" wire:target="submitReply({{ $question->id }})"
                                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-full bg-[#1E3D59] px-4 py-2.5 text-sm font-semibold text-[#FDFBF7] transition hover:bg-[#16293F] disabled:cursor-not-allowed disabled:opacity-60">
                                        <x-spinner wire:loading wire:target="submitReply({{ $question->id }})" />
                                        Répondre
                                    </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $questions->links() }}
        </div>
    @endif
</div>