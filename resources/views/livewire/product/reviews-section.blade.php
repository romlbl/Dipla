<div class="mt-10">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-[#1E293B]">Avis</h2>
        @if($reviews->isNotEmpty())
            <span class="text-sm text-[#333333]">
                ★ {{ $product->averageRating() }} · {{ $reviews->count() }} avis
            </span>
        @endif
    </div>

    <div class="flex flex-col lg:flex-row gap-6">

        {{-- Liste des avis : en haut sur mobile, à droite sur desktop, hauteur limitée --}}
        <div class="order-1 lg:order-2 lg:w-1/2 max-h-[420px] overflow-y-auto pr-1 flex flex-col gap-3">
            @forelse($reviews as $review)
                <div class="rounded-xl border border-[#E2E8F0] bg-[#FAFAFF] p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-[#1E293B]">{{ $review->user->name ?? 'Utilisateur' }}</p>
                        <span class="shrink-0 inline-flex items-center gap-1 rounded-full bg-[#1E3D59]/10 px-2.5 py-1 text-xs font-mono font-semibold text-[#1E3D59]">
                            ★ {{ $review->rating }}
                        </span>
                    </div>
                    <p class="text-sm font-medium text-[#1E293B] mt-2">{{ $review->subject }}</p>
                    <p class="text-sm text-[#333333]/80 mt-1">{{ $review->content }}</p>

                    @if($review->company_reply)
                        <div class="mt-3 rounded-lg border-l-4 border-[#1E3D59] bg-white p-3">
                            <p class="text-xs font-semibold text-[#1E3D59] mb-1">Réponse du commerce</p>
                            <p class="text-sm text-[#333333]/80">{{ $review->company_reply }}</p>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-sm text-[#333333]/60">Sois le premier à laisser un avis.</p>
            @endforelse
        </div>

        {{-- Formulaire : en bas sur mobile, à gauche sur desktop --}}
        <div class="order-2 lg:order-1 lg:w-1/2">
            @auth
                <h3 class="text-sm font-semibold text-[#1E293B] mb-2">Laisser un avis</h3>

                @if($errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 mb-3">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form wire:submit="submitReview" class="flex flex-col gap-3">
                    <div class="flex gap-1">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" wire:click="$set('rating', {{ $i }})"
                                    class="text-2xl {{ $i <= $rating ? 'text-[#1E3D59]' : 'text-[#E2E8F0]' }}">
                                ★
                            </button>
                        @endfor
                    </div>

                    <input type="text" wire:model="subject" placeholder="Résumez votre avis en quelques mots"
                           class="w-full rounded-xl border border-[#E2E8F0] bg-[#FDFBF7] px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20">

                    <textarea wire:model="content" rows="4" placeholder="Votre expérience avec ce produit/service..."
                              class="w-full rounded-xl border border-[#E2E8F0] bg-[#FDFBF7] px-4 py-2.5 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/20"></textarea>

                <button type="submit" wire:loading.attr="disabled" wire:target="submitReview"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-full bg-[#1E3D59] px-4 py-2.5 text-sm font-semibold text-[#FDFBF7] transition hover:bg-[#16293F] disabled:cursor-not-allowed disabled:opacity-60">
                    <x-spinner wire:loading wire:target="submitReview" />
                    Publier
                </button>
                </form>
            @else
                <div class="rounded-xl border border-dashed border-[#E2E8F0] p-5 text-sm text-[#333333]/60">
                    <a href="{{ route('login') }}" wire:navigate class="text-[#1E3D59] font-medium hover:underline">Connectez-vous</a>
                    pour laisser un avis.
                </div>
            @endauth
        </div>
    </div>
</div>