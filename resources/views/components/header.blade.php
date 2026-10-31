<header class="ch-site-header sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20 gap-4">
            <!-- Brand -->
            <a href="{{ route('home') }}" class="group flex items-center gap-3 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00C2A8] focus-visible:ring-offset-2 focus-visible:ring-offset-[#111318] rounded-lg p-1 min-w-0" aria-label="{{ config('app.name', 'Chifheto') }} - Beranda">
                <span class="ch-brand-mark" aria-hidden="true">C.</span>
                <span class="flex flex-col min-w-0">
                    <span class="text-base sm:text-lg font-black tracking-tight text-white truncate group-hover:text-[#00C2A8] transition-colors">{{ config('app.name', 'Chifheto') }}</span>
                    <span class="hidden sm:block text-[11px] text-[#A0AAB8] font-medium truncate">Pelatihan teknis &amp; profesional</span>
                </span>
            </a>

            <!-- Desktop navigation -->
            <nav class="hidden lg:flex items-center gap-7" role="navigation" aria-label="Navigasi Utama Desktop">
                @foreach ([
                    ['Beranda', route('home')],
                    ['Katalog', route('catalog.index')],
                    ['Artikel', route('articles.index')],
                    ['Wilayah', route('home') . '#wilayah'],
                ] as $item)
                    @php $isHome = $item[0] === 'Beranda' && request()->routeIs('home'); @endphp
                    <a href="{{ $item[1] }}"
                       @if($isHome) aria-current="page" @endif
                       class="text-sm font-semibold py-2 border-b-2 {{ $isHome ? 'text-white border-[#00C2A8]' : 'text-[#C7CBD2] border-transparent hover:text-white hover:border-[#00C2A8]/50' }} transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00C2A8] rounded">
                        {{ $item[0] }}
                    </a>
                @endforeach
            </nav>

            <!-- CTA (Desktop) -->
            <div class="hidden sm:flex items-center">
                <x-wa-cta variant="primary" size="sm" label="Konsultasi WhatsApp" class="bg-[#00C2A8] hover:bg-[#00A991] text-[#111318] hover:text-[#111318] font-bold shadow-md" />
            </div>

            <!-- Mobile Menu Toggle -->
            <div class="flex lg:hidden items-center">
                <button type="button"
                        id="mobile-nav-toggle"
                        aria-controls="mobile-nav-drawer"
                        aria-expanded="false"
                        aria-label="Buka menu navigasi utama"
                        class="p-2.5 rounded-lg text-white hover:text-[#00C2A8] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00C2A8]">
                    <svg id="icon-menu-open" class="w-6 h-6 block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg id="icon-menu-close" class="w-6 h-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Drawer -->
    <div id="mobile-nav-drawer"
         class="hidden lg:hidden bg-[#111318] border-t border-white/10 px-4 pt-2 pb-6"
         role="navigation"
         aria-label="Navigasi Utama Seluler">
        <div class="flex flex-col space-y-1 pt-2">
            @foreach ([
                ['Beranda', route('home')],
                ['Katalog', route('catalog.index')],
                ['Artikel', route('articles.index')],
                ['Wilayah', route('home') . '#wilayah'],
            ] as $item)
                <a href="{{ $item[1] }}" class="mobile-nav-link text-base font-semibold text-[#E2E8F0] hover:text-[#00C2A8] hover:bg-white/5 px-3 py-2.5 rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00C2A8]">
                    {{ $item[0] }}
                </a>
            @endforeach
            <div class="pt-3 border-t border-white/10">
                <x-wa-cta variant="primary" size="md" class="w-full justify-center bg-[#00C2A8] hover:bg-[#00A991] text-[#111318] hover:text-[#111318] font-bold" label="Konsultasi Cepat via WhatsApp" />
            </div>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('mobile-nav-toggle');
        const drawer = document.getElementById('mobile-nav-drawer');
        const openIcon = document.getElementById('icon-menu-open');
        const closeIcon = document.getElementById('icon-menu-close');
        const mobileLinks = document.querySelectorAll('.mobile-nav-link');

        if (toggleBtn && drawer) {
            toggleBtn.addEventListener('click', function () {
                const isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';
                toggleBtn.setAttribute('aria-expanded', !isExpanded);
                drawer.classList.toggle('hidden');
                openIcon.classList.toggle('hidden');
                closeIcon.classList.toggle('hidden');
                toggleBtn.setAttribute('aria-label', isExpanded ? 'Buka menu navigasi utama' : 'Tutup menu navigasi utama');
            });

            mobileLinks.forEach(function (link) {
                link.addEventListener('click', function () {
                    drawer.classList.add('hidden');
                    openIcon.classList.remove('hidden');
                    closeIcon.classList.add('hidden');
                    toggleBtn.setAttribute('aria-expanded', 'false');
                });
            });
        }
    });
</script>
