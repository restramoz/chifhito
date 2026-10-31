
@extends('layouts.app')

@section('title', 'Skema Kompetensi ' . config('app.name', 'Chifheto') . ' Provinsi ' . $province['name'] . ' — Konsultasi Resmi')
@section('meta_description', 'Informasi skema kompetensi dan program pelatihan ' . config('app.name', 'Chifheto') . ' untuk wilayah Provinsi ' . $province['name'] . '.')
@section('wa_message', $waMessage)

@section('content')
    {{-- Breadcrumb --}}
    <div class="bg-neutral-100 border-b border-neutral-200">
        <x-breadcrumb :items="[
            ['label' => 'Wilayah', 'url' => null],
            ['label' => $province['name'], 'url' => null],
        ]" />
    </div>

    {{-- Hero --}}
    <section class="relative bg-industry-black text-white overflow-hidden border-b border-dark-mahogany"
        aria-labelledby="prov-hero-heading">

        <div class="absolute inset-0 opacity-10 pointer-events-none" aria-hidden="true">
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="grid-p" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#FFF" stroke-width="1" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid-p)" />
            </svg>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20">
            <div class="max-w-3xl">

                {{-- Mobile hero image --}}
                <div class="lg:hidden mb-5 -mx-4 sm:-mx-6">
                    <img
                        src="/images/home/hero-industrial.jpg"
                        alt="Aktivitas {{ config('app.name', 'Chifheto') }} di Provinsi {{ $province['name'] }}"
                        width="1600"
                        height="900"
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                        class="hero-image-mobile w-full object-cover object-center rounded-xl"
                    />
                </div>

                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-burnt-brick border border-burnt-brick/80 text-white text-xs sm:text-sm font-semibold mb-5 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-safety-orange animate-pulse"></span>
                    Informasi Skema &amp; Layanan Konsultasi
                </div>

                <h1 id="prov-hero-heading"
                    class="font-extrabold tracking-tight text-white leading-tight uppercase text-[clamp(1.75rem,4.5vw,2.5rem)] sm:text-[clamp(2rem,5vw,3rem)] lg:text-[clamp(2.25rem,4.5vw,3.5rem)]">
                    Skema Kompetensi<br>
                    <span class="text-safety-orange">
                        {{ config('app.name', 'Chifheto') }} Provinsi {{ $province['name'] }}
                    </span>
                </h1>

                <p class="mt-6 text-base sm:text-lg text-neutral-200 leading-relaxed">
                    Pusat informasi skema kompetensi dan program pelatihan {{ config('app.name', 'Chifheto') }} untuk badan usaha,
                    fasilitas produksi, dan mitra industri di
                    <strong class="text-white">Provinsi {{ $province['name'] }}</strong>.
                </p>

                <div class="mt-7 flex flex-col sm:flex-row gap-3 sm:gap-4 w-full">
                    <x-wa-cta
                        variant="primary"
                        size="lg"
                        :message="$waMessage"
                        label="Konsultasi Wilayah {{ $province['name'] }}"
                        class="w-full sm:w-auto bg-safety-orange hover:bg-burnt-brick text-industry-black hover:text-white font-bold shadow-lg"
                    />
                </div>

                <div class="mt-8 pt-6 border-t border-neutral-800 grid grid-cols-2 gap-4 sm:gap-6">
                    <div class="min-w-0">
                        <div class="text-[10px] sm:text-xs text-neutral-400 uppercase tracking-wider">Pulau</div>
                        <div class="text-sm sm:text-base font-extrabold text-white mt-0.5">
                            {{ $province['island'] }}
                        </div>
                    </div>

                    <div class="min-w-0">
                        <div class="text-[10px] sm:text-xs text-neutral-400 uppercase tracking-wider">Kab/Kota</div>
                        <div class="text-sm sm:text-base font-extrabold text-safety-orange mt-0.5">
                            {{ $province['total_cities'] }}
                        </div>
                    </div>

                    <div class="min-w-0">
                        <div class="text-[10px] sm:text-xs text-neutral-400 uppercase tracking-wider">Layanan</div>
                        <div class="text-sm sm:text-base font-extrabold text-white mt-0.5">
                            {{ count($programs) }} Layanan
                        </div>
                    </div>

                    <div class="min-w-0">
                        <div class="text-[10px] sm:text-xs text-neutral-400 uppercase tracking-wider">Lingkup</div>
                        <div class="text-sm sm:text-base font-extrabold text-safety-orange mt-0.5">
                            {{ config('app.name', 'Chifheto') }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- Disclaimer --}}
    <section class="py-8 sm:py-10 bg-neutral-50 border-b border-neutral-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-start gap-4">
                <svg class="w-6 h-6 text-burnt-brick shrink-0 mt-0.5"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>

                <p class="text-sm text-dark-mahogany leading-relaxed">
                    <strong class="text-industry-black">Catatan penting:</strong>
                    Halaman ini adalah portal informasi dan konsultasi program.
                    Penyelenggaraan pelatihan tatap muka, in-house training di kota/kabupaten
                    dalam Provinsi {{ $province['name'] }}, jadwal batch, dan persyaratan administrasi
                    dikoordinasikan secara langsung melalui tim konsultan kami.
                </p>
            </div>
        </div>
    </section>

    {{-- Program Links: show 6 initially --}}
    <section id="program"
        class="py-12 sm:py-16 lg:py-20 bg-white border-b border-neutral-200"
        aria-labelledby="prov-program-heading">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="max-w-3xl mb-8 sm:mb-10">
                <span class="text-xs font-bold uppercase tracking-wider text-burnt-brick bg-neutral-100 px-3 py-1 rounded-full border border-burnt-brick/20">
                    Layanan Unggulan
                </span>

                <h2 id="prov-program-heading"
                    class="mt-3 font-extrabold text-industry-black tracking-tight text-[clamp(1.5rem,4vw,2rem)] sm:text-[clamp(1.75rem,4.5vw,2.25rem)]">
                    Skema Kompetensi &amp; Layanan di {{ $province['name'] }}
                </h2>

                <p class="mt-3 text-sm sm:text-base text-dark-mahogany leading-relaxed">
                    Pilih program pelatihan {{ config('app.name', 'Chifheto') }} sesuai kebutuhan organisasi Anda.
                    Gunakan tombol di bawah untuk melihat seluruh layanan yang tersedia.
                </p>
            </div>

            @php
                $initialProgramLimit = 6;
                $totalPrograms = count($programs);
            @endphp

            <div id="province-program-grid"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">

                @foreach ($programs as $prog)
                    <article
                        class="province-program-item {{ $loop->index >= $initialProgramLimit ? 'hidden' : '' }}"
                        @if ($loop->index >= $initialProgramLimit) hidden @endif>

                        <a href="{{ route('regional.province.program', ['province' => $province['slug'], 'program' => $prog['slug']]) }}"
                            class="group h-full flex flex-col bg-white rounded-xl border border-neutral-200 hover:border-burnt-brick hover:shadow-md transition-all overflow-hidden focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-burnt-brick">

                            <div class="h-1.5 bg-safety-orange"></div>

                            <div class="p-5 sm:p-6 flex-1 flex flex-col">

                                <div class="text-xs font-bold uppercase tracking-wider text-burnt-brick mb-2">
                                    {{ $prog['category_label'] ?? 'Layanan' }}
                                </div>

                                <h3 class="text-base sm:text-lg font-bold text-industry-black mb-2 group-hover:text-burnt-brick transition-colors leading-snug">
                                    {{ $prog['title'] }}
                                </h3>

                                <p class="text-xs sm:text-sm text-dark-mahogany flex-1 leading-relaxed line-clamp-3">
                                    {{ $prog['hero_subheading'] ?? ('Informasi program pelatihan dan konsultasi ' . config('app.name', 'Chifheto') . '.') }}
                                </p>

                                <div class="mt-4 pt-4 border-t border-neutral-100 text-xs font-bold text-burnt-brick flex items-center justify-between">
                                    <span>Detail Program</span>
                                    <span aria-hidden="true">&rarr;</span>
                                </div>

                            </div>
                        </a>
                    </article>
                @endforeach

            </div>

            {{-- Show all / hide button --}}
            @if ($totalPrograms > $initialProgramLimit)
                <div class="mt-8 sm:mt-10 flex justify-center">
                    <button
                        type="button"
                        id="province-program-toggle"
                        class="inline-flex items-center justify-center gap-2 px-5 sm:px-7 py-3 min-h-[48px] rounded-xl border-2 border-burnt-brick text-sm sm:text-base font-bold text-burnt-brick hover:bg-burnt-brick hover:text-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-burnt-brick focus-visible:ring-offset-2"
                        aria-expanded="false"
                        aria-controls="province-program-grid"
                        data-total="{{ $totalPrograms }}"
                        data-limit="{{ $initialProgramLimit }}">

                        <span id="province-program-toggle-label">
                            Lihat Semua {{ $totalPrograms }} Layanan
                        </span>

                        <svg id="province-program-toggle-icon"
                            class="w-4 h-4 transition-transform"
                            viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </button>
                </div>
            @endif

        </div>
    </section>

    {{-- Cities in Province --}}
    @if (count($province['cities']) > 0)
        <section id="kota"
            class="py-12 sm:py-16 lg:py-20 bg-neutral-50 border-b border-neutral-200"
            aria-labelledby="prov-kota-heading">

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mb-8">
                    <span class="text-xs font-bold uppercase tracking-wider text-burnt-brick">
                        Cakupan Wilayah
                    </span>

                    <h2 id="prov-kota-heading"
                        class="mt-2 text-xl sm:text-2xl font-extrabold text-industry-black">
                        Kabupaten &amp; Kota di Provinsi {{ $province['name'] }}
                    </h2>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                    @foreach ($province['cities'] as $c)
                        <a href="{{ route('regional.city', $c['slug']) }}"
                            class="block px-3 py-3 rounded-lg bg-white border border-neutral-200 hover:border-burnt-brick hover:bg-neutral-100 text-sm font-medium text-dark-mahogany hover:text-burnt-brick transition-colors truncate focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-burnt-brick">
                            {{ $c['name'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- FAQ --}}
    <section id="faq"
        class="py-12 sm:py-16 lg:py-20 bg-white border-b border-neutral-200"
        aria-labelledby="prov-faq-heading">

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center mb-8 sm:mb-12">
                <span class="text-xs font-bold uppercase tracking-wider text-burnt-brick bg-neutral-100 px-3 py-1 rounded-full border border-burnt-brick/20">
                    Tanya Jawab
                </span>

                <h2 id="prov-faq-heading"
                    class="mt-3 text-2xl sm:text-3xl font-extrabold text-industry-black tracking-tight">
                    FAQ: Skema Kompetensi di {{ $province['name'] }}
                </h2>
            </div>

            <div class="space-y-4">
                @foreach ([
                    [
                        'q' => 'Apakah skema kompetensi dan layanan konsultasi tersedia untuk wilayah Provinsi ' . $province['name'] . '?',
                        'a' => 'Ya, halaman ini menyajikan skema kompetensi ' . config('app.name', 'Chifheto') . ' yang tersedia. Layanan konsultasi dapat dikonfirmasi untuk kota dan kabupaten di Provinsi ' . $province['name'] . '.',
                    ],
                    [
                        'q' => 'Skema kompetensi apa saja yang tersedia?',
                        'a' => 'Daftar program tersedia pada bagian katalog di halaman ini. Buka seluruh layanan untuk melihat daftar lengkap dan pilih salah satu program untuk membaca rinciannya.',
                    ],
                    [
                        'q' => 'Bagaimana cara pengajuan konsultasi untuk kebutuhan di ' . $province['name'] . '?',
                        'a' => 'Hubungi tim kami melalui WhatsApp. Kebutuhan program, profil kegiatan usaha, dan persyaratan administrasi dapat didiskusikan sebelum proposal disiapkan.',
                    ],
                ] as $faq)

                    <details class="group bg-white rounded-xl border border-neutral-200 p-5 sm:p-6 open:border-burnt-brick shadow-sm">
                        <summary class="flex justify-between items-center gap-4 font-bold text-industry-black cursor-pointer list-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-burnt-brick rounded">
                            <span>{{ $faq['q'] }}</span>

                            <span class="w-7 h-7 rounded-full bg-neutral-100 flex items-center justify-center shrink-0 group-open:rotate-180 transition-transform">
                                <svg class="w-4 h-4 text-burnt-brick"
                                    fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2.5"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </summary>

                        <p class="mt-4 pt-4 border-t border-neutral-100 text-sm text-dark-mahogany leading-relaxed">
                            {{ $faq['a'] }}
                        </p>
                    </details>

                @endforeach
            </div>
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="py-14 sm:py-20 lg:py-24 bg-industry-black text-white"
        aria-labelledby="prov-cta-heading">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="max-w-3xl mx-auto">

                <h2 id="prov-cta-heading"
                    class="text-2xl sm:text-3xl font-extrabold uppercase tracking-tight">
                    Konsultasi Program {{ config('app.name', 'Chifheto') }}<br>
                    <span class="text-safety-orange">
                        Provinsi {{ $province['name'] }}
                    </span>
                </h2>

                <p class="mt-4 text-base text-neutral-200 leading-relaxed">
                    Hubungi kami untuk informasi program pelatihan dan kebutuhan konsultasi di Provinsi
                    {{ $province['name'] }}.
                </p>

                <div class="mt-7 flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 w-full">
                    <x-wa-cta
                        variant="primary"
                        size="lg"
                        :message="$waMessage"
                        label="Konsultasi Sekarang"
                        class="w-full sm:w-auto bg-safety-orange hover:bg-burnt-brick text-industry-black hover:text-white font-bold shadow-lg"
                    />

                    <x-button
                        :href="route('home')"
                        variant="outline"
                        size="lg"
                        class="w-full sm:w-auto text-white border-2 border-neutral-400 hover:bg-neutral-800">
                        Kembali ke Beranda
                    </x-button>
                </div>

            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.getElementById('province-program-toggle');
    const programGrid = document.getElementById('province-program-grid');

    if (!toggleButton || !programGrid) {
        return;
    }

    const label = document.getElementById('province-program-toggle-label');
    const icon = document.getElementById('province-program-toggle-icon');
    const items = Array.from(
        programGrid.querySelectorAll('.province-program-item')
    );

    const limit = Number(toggleButton.dataset.limit || 6);
    const total = items.length;
    let expanded = false;

    function updateProgramList() {
        items.forEach(function (item, index) {
            item.hidden = !expanded && index >= limit;
        });

        toggleButton.setAttribute('aria-expanded', String(expanded));

        label.textContent = expanded
            ? 'Sembunyikan Layanan'
            : 'Lihat Semua ' + total + ' Layanan';

        if (icon) {
            icon.style.transform = expanded
                ? 'rotate(180deg)'
                : 'rotate(0deg)';
        }
    }

    toggleButton.addEventListener('click', function () {
        expanded = !expanded;
        updateProgramList();

        if (!expanded) {
            document.getElementById('program').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });

    updateProgramList();
});
</script>
@endpush
