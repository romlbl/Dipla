{{--
    Bandeau d'information : Dipla n'utilise que des traceurs indispensables ou demandés par l'utilisateur
    (session, CSRF, position choisie), donc pas de choix "accepter / refuser" (voir politique de confidentialité).
    L'état "vu" est mémorisé dans localStorage.
--}}
<div x-data="{ show: false }"
     x-init="try { show = localStorage.getItem('dipla-notice-seen') !== '1'; } catch (e) { show = true; }"
     x-show="show" x-cloak
     role="region" aria-label="Information sur les cookies"
     class="fixed inset-x-0 bottom-0 z-[70] px-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:px-4 sm:pb-4">
    <div class="mx-auto flex max-w-3xl flex-col gap-3 rounded-2xl border border-[#E2E8F0] bg-[#FAFAFF] p-4 shadow-lg sm:flex-row sm:items-center sm:gap-5">
        <p class="text-sm leading-relaxed text-[#333333]">
            Dipla n'utilise que des cookies indispensables (connexion, sécurité) et retient sur votre appareil la position de recherche que vous choisissez.
            Aucun suivi publicitaire ni statistique.
            <a href="{{ route('legal.privacy') }}" wire:navigate class="font-medium text-[#1E3D59] underline underline-offset-2">En savoir plus</a>
        </p>
        <button type="button"
                @click="try { localStorage.setItem('dipla-notice-seen', '1'); } catch (e) {} show = false"
                class="min-h-[44px] shrink-0 rounded-full bg-[#1E3D59] px-6 py-2.5 text-sm font-semibold text-[#FDFBF7] transition hover:bg-[#16293F]">
            Compris
        </button>
    </div>
</div>