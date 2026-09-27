<div class="mt-10">
    <h2 class="text-sm font-semibold text-[#1E293B] mb-3">Questions</h2>

    <div class="flex flex-col lg:flex-row gap-6">

        {{-- Liste des questions : en haut sur mobile, à droite sur desktop, hauteur limitée --}}
        <div class="order-1 lg:order-2 lg:w-1/2 max-h-[420px] overflow-y-auto pr-1 flex flex-col gap-3">
            @forelse($questions as $question)
                <div class="rounded-xl border border-[#E2E8F0] bg-[#FAFAFF] p-4 shadow-sm">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-semibold text-[#1E293B]">{{ $question->user->name ?? 'Utilisateur' }}</p>
                        <span class="text-xs text-[#333333]/50">{{ $question->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-[#333333] mt-1">{{ $question->content }}</p>

                    {{-- Réponses --}}
                    @foreach($question->replies as $reply)
                        <div class="mt-3 rounded-lg border-l-4 border-[#1E3D59] bg-white p-3">
                            <p class="text-sm font-medium text-[#1E3D59]">
                                {{ $reply->user->name ?? $product->company->name }}
                                @if(!$reply->user)
                                    <span class="text-xs font-normal text-[#4A3B5C]">· Réponse du commerce</span>
                                @endif
                            </p>
                            <p class="text-sm text-[#333333] mt-1">{{ $reply->content }}</p>
                        </div>
                    @endforeach

                    {{-- Formulaire de réponse --}}
                    @auth
                        <div class="mt-3">
                            @if(!($showReplyForm[$question->id] ?? false))
                                <button wire:click="toggleReplyForm({{ $question->id }})" class="text-sm text-[#1E3D59] font-medium hover:underline">
                                    Répondre
                                </button>
                            @else
                                <div class="flex gap-2 mt-2">
                                    <input type="text" wire:model="replyContent.{{ $question->id }}"
                                           placeholder="Votre réponse..."
                                           class="flex-1 rounded-xl border border-[#E2E8F0] bg-white px-4 py-2 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20">
                                    <button type="button" wire:click="submitReply({{ $question->id }})"
                                        wire:loading.attr="disabled" wire:target="submitReply({{ $question->id }})"
                                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-full bg-[#1E3D59] px-4 py-2.5 text-sm font-semibold text-[#FDFBF7] transition hover:bg-[#16293F] disabled:cursor-not-allowed disabled:opacity-60">
                                        <x-spinner wire:loading wire:target="submitReply({{ $question->id }})" />
                                        Envoyer
                                    </button>
                                </div>
                                @error("replyContent.{$question->id}")
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>
                    @endauth
                </div>
            @empty
                <p class="text-sm text-[#333333]/60">Aucune question pour l'instant.</p>
            @endforelse
        </div>

        {{-- Formulaire nouvelle question : en bas sur mobile, à gauche sur desktop --}}
        <div class="order-2 lg:order-1 lg:w-1/2">
            @auth
                <h3 class="text-sm font-semibold text-[#1E293B] mb-2">Poser une question</h3>

                @if($errors->has('auth'))
                    <p class="text-sm text-red-600 mb-2">{{ $errors->first('auth') }}</p>
                @endif

                <div class="flex flex-col gap-2">
                    <textarea id="new-question-input" wire:model="newQuestion" rows="4"
                              placeholder="Pose une question sur ce produit/service..."
                              class="w-full rounded-xl border border-[#E2E8F0] bg-[#FDFBF7] px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20"></textarea>
                <button type="button" wire:click="submitQuestion"
                        wire:loading.attr="disabled" wire:target="submitQuestion"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-full bg-[#1E3D59] px-4 py-2.5 text-sm font-semibold text-[#FDFBF7] transition hover:bg-[#16293F] disabled:cursor-not-allowed disabled:opacity-60">
                    <x-spinner wire:loading wire:target="submitQuestion" />
                    Envoyer
                </button>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-[#E2E8F0] p-5 text-sm text-[#333333]/60">
                    <a href="{{ route('login') }}" wire:navigate class="text-[#1E3D59] font-medium hover:underline">Connectez-vous</a>
                    pour poser une question.
                </div>
            @endauth
        </div>
    </div>
</div>