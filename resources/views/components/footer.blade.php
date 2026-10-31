<footer class="bg-[#111318] text-white border-t border-white/10" role="contentinfo" aria-label="Informasi Kaki Halaman">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <!-- Identity -->
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="ch-brand-mark" aria-hidden="true">C.</span>
                    <span class="flex flex-col min-w-0">
                        <span class="text-base font-extrabold text-white tracking-tight truncate">{{ config('app.name', 'Chifheto') }}</span>
                        <span class="text-[11px] text-[#A0AAB8] font-medium">Pelatihan profesional</span>
                    </span>
                </div>
                <p class="text-sm text-[#A0AAB8] leading-relaxed">
                    Referensi pelatihan profesional untuk bidang teknis dan industri. Telusuri katalog berdasarkan topik, lalu bicarakan kebutuhan tim Anda.
                </p>
            </div>

            <!-- Navigation -->
            <nav aria-label="Navigasi Kaki Halaman">
                <h3 class="text-xs font-bold text-[#00C2A8] uppercase tracking-wider mb-4">Navigasi Utama</h3>
                <ul class="space-y-2.5 text-sm text-[#C7CBD2]" role="list">
                    <li><a href="{{ route('home') }}" class="hover:text-[#00C2A8] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00C2A8] rounded">Beranda</a></li>
                    <li><a href="{{ route('catalog.index') }}" class="hover:text-[#00C2A8] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00C2A8] rounded">Katalog Pelatihan</a></li>
                    <li><a href="{{ route('articles.index') }}" class="hover:text-[#00C2A8] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00C2A8] rounded">Artikel &amp; Wawasan</a></li>
                    <li><a href="{{ route('home') }}#kategori" class="hover:text-[#00C2A8] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00C2A8] rounded">Bidang pelatihan</a></li>
                </ul>
            </nav>

            <!-- Contact -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold text-[#00C2A8] uppercase tracking-wider">Konsultasi Layanan</h3>
                <p class="text-sm text-[#A0AAB8] leading-relaxed">Informasi jadwal pelaksanaan, persyaratan peserta, dan konfirmasi materi program dapat dikonsultasikan langsung melalui WhatsApp.</p>
                <div>
                    <x-wa-cta variant="primary" size="sm" label="Konsultasi via WhatsApp" class="bg-[#00C2A8] hover:bg-[#00A991] text-[#111318] hover:text-[#111318] font-bold shadow-md" />
                </div>
            </div>
        </div>

        <div class="mt-12 pt-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-[#8E97A4]">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Chifheto') }}. Seluruh hak cipta dilindungi.</p>
            <p>Konsultasikan kebutuhan spesifik Anda bersama tim profesional kami.</p>
        </div>
    </div>
</footer>
