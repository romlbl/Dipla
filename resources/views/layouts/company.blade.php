<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Espace entreprise - Dipla')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#FBF9F8] text-[#333333] font-sans antialiased">
    @php
        $company = auth('company')->user();
        $pendingQuestionsCount = $company->pendingQuestionsCount();
        $isOpen = $company->isOpenNow();
    @endphp
    {{-- Sidebar desktop --}}
    <aside class="hidden md:flex md:flex-col md:w-64 md:fixed md:inset-y-0 md:left-0 bg-[#252623] text-[#E4E2DE] z-30">
        <div class="flex items-center gap-3 px-6 py-6 border-b border-white/10">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#1E3D59]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75L12 3l9 6.75V21a.75.75 0 01-.75.75H15a.75.75 0 01-.75-.75v-4.5a2.25 2.25 0 00-4.5 0V21a.75.75 0 01-.75.75H3.75A.75.75 0 013 21V9.75z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-white">Dipla Pro</p>
                <p class="text-xs text-white/50">Portail Commerçant</p>
            </div>
        </div>

        <nav class="flex-1 flex flex-col gap-1 px-3 py-6">
            <a href="{{ route('company.dashboard') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('company.dashboard') ? 'bg-[#1E3D59] text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                </svg>
                Devanture &amp; Accueil
            </a>
            <a href="{{ route('company.products.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('company.products.*') ? 'bg-[#1E3D59] text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                Catalogue Produits
            </a>
            <a href="{{ route('company.reviews.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('company.reviews.*') ? 'bg-[#1E3D59] text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.562.562 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                </svg>
                Avis Clients
            </a>
            <a href="{{ route('company.questions.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('company.questions.*') ? 'bg-[#1E3D59] text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                </svg>
                <span class="flex-1">Questions</span>
                @if(($pendingQuestionsCount ?? 0) > 0)
                    <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-bold text-white">
                        {{ $pendingQuestionsCount }}
                    </span>
                @endif
            </a>
            <a href="{{ route('company.settings.edit') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('company.settings.*') ? 'bg-[#1E3D59] text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Paramètres
            </a>
        </nav>

        <div class="px-3 pb-6">
            <form method="POST" action="{{ route('company.logout') }}">
                @csrf
                <button type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-full bg-[#4A3B5C] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#3a2f49]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                    Déconnexion
                </button>
            </form>
        </div>
    </aside>

    {{-- Barre mobile --}}
    <div class="md:hidden fixed top-0 inset-x-0 z-30 flex items-center justify-between border-b border-[#E2E8F0] bg-[#FAFAFF] px-4 py-3">
        <a href="{{ route('company.dashboard') }}" class="text-lg font-semibold text-[#1E293B]">Dipla Pro</a>
        <button id="company-menu-toggle" class="p-2 text-[#1E293B]" aria-label="Ouvrir le menu">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>
    <nav id="company-menu-mobile" class="hidden md:hidden fixed top-14 inset-x-0 z-30 flex-col gap-1 bg-[#FAFAFF] border-b border-[#E2E8F0] px-4 py-3 text-sm font-medium">
        <a href="{{ route('company.dashboard') }}" class="block rounded-full px-4 py-2.5 {{ request()->routeIs('company.dashboard') ? 'bg-[#1E3D59] text-[#FDFBF7]' : 'text-[#1E293B]' }}">Devanture &amp; Accueil</a>
        <a href="{{ route('company.products.index') }}" class="block rounded-full px-4 py-2.5 {{ request()->routeIs('company.products.*') ? 'bg-[#1E3D59] text-[#FDFBF7]' : 'text-[#1E293B]' }}">Catalogue Produits</a>
        <a href="{{ route('company.reviews.index') }}" class="block rounded-full px-4 py-2.5 {{ request()->routeIs('company.reviews.*') ? 'bg-[#1E3D59] text-[#FDFBF7]' : 'text-[#1E293B]' }}">Avis Clients</a>
                <a href="{{ route('company.questions.index') }}" class="flex items-center justify-between rounded-full px-4 py-2.5 {{ request()->routeIs('company.questions.*') ? 'bg-[#1E3D59] text-[#FDFBF7]' : 'text-[#1E293B]' }}">
            <span>Questions</span>
            @if(($pendingQuestionsCount ?? 0) > 0)
                <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-bold text-white">
                    {{ $pendingQuestionsCount }}
                </span>
            @endif
        </a>
        <a href="{{ route('company.settings.edit') }}" class="block rounded-full px-4 py-2.5 {{ request()->routeIs('company.settings.*') ? 'bg-[#1E3D59] text-[#FDFBF7]' : 'text-[#1E293B]' }}">Paramètres</a>
        <form method="POST" action="{{ route('company.logout') }}" class="pt-1">
            @csrf
            <button type="submit" class="w-full text-left rounded-full bg-[#4A3B5C] px-4 py-2.5 text-white">Déconnexion</button>
        </form>
    </nav>

    {{-- Contenu --}}
    <div class="md:pl-64">
        {{-- Top bar desktop --}}
        <header class="hidden md:flex sticky top-0 z-20 h-16 items-center justify-between border-b border-[#E2E8F0] bg-[#FDFBF7] px-8">
            <form action="{{ route('company.products.index') }}" method="GET" class="relative w-72">
                <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#333333]/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                </svg>
                <input type="text" name="search" placeholder="Rechercher un produit..."
                       value="{{ request()->routeIs('company.products.index') ? request('search') : '' }}"
                       class="w-full rounded-full border border-[#E2E8F0] bg-white py-2 pl-10 pr-4 text-sm text-[#333333] focus:border-[#1E3D59] focus:outline-none focus:ring-2 focus:ring-[#1E3D59]/10">
            </form>

            @php
                $isOpen = auth('company')->user()->isOpenNow();
            @endphp
            <div class="flex items-center gap-2 rounded-full border border-[#E2E8F0] bg-white px-4 py-2">
                <span class="h-2 w-2 rounded-full {{ $isOpen === true ? 'bg-emerald-500' : ($isOpen === false ? 'bg-red-400' : 'bg-[#333333]/30') }}"></span>
                <span class="text-sm font-medium text-[#1E293B]">
                    @if($isOpen === true)
                        Ouvert aux clients
                    @elseif($isOpen === false)
                        Actuellement fermé 
                    @else
                        Horaires non renseignés
                    @endif
                </span>
            </div>
        </header>

        <main class="px-4 pt-3 pb-6 md:px-8 md:pt-4 md:pb-10 mt-14 md:mt-0">
            {{ $slot }}
        </main>
    </div>

    <script>
        document.getElementById('company-menu-toggle').addEventListener('click', () => {
            document.getElementById('company-menu-mobile').classList.toggle('hidden');
            document.getElementById('company-menu-mobile').classList.toggle('flex');
        });
    </script>
    @fluxScripts
</body>
</html>