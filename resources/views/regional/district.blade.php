@extends('layouts.app')

{{-- District pages are intentionally noindex: content is thin until meaningful local detail is available --}}
@section('robots', 'noindex, follow')

@section('title', 'Skema Kompetensi ' . config('app.name', 'Najkaltare') . ' — Kecamatan ' . $district['name'] . ', ' . $district['city_name'])
@section('meta_description', 'Halaman referensi wilayah administratif Kecamatan ' . $district['name'] . ' di ' . $district['city_name'] . '. Konsultasi skema kompetensi ' . config('app.name', 'Najkaltare') . ' tersedia melalui WhatsApp.')
@section('wa_message', $waMessage)

@section('content')
<noscript>
    <style>.scheme-extra[hidden]{display:block!important;}</style>
</noscript>
    {{-- Breadcrumb --}}
    <div class="bg-neutral-100 border-b border-neutral-200">
        <x-breadcrumb :items="[
            ['label' => 'Wilayah', 'url' => null],
            ['label' => $district['province'], 'url' => route('regional.province', $district['province_slug'])],
            ['label' => $district['city_name'], 'url' => route('regional.city', $district['city_slug'])],
            ['label' => $district['name'], 'url' => null],
        ]" />
    </div>

    {{-- Hero (compact — thin page) --}}
    <section class="relative bg-industry-black text-white overflow-hidden border-b border-dark-mahogany" aria-labelledby="dist-hero-heading">
        <div class="absolute inset-0 opacity-10 pointer-events-none" aria-hidden="true">
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs><pattern id="grid-d" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="#FFF" stroke-width="1"/></pattern></defs>
                <rect width="100%" height="100%" fill="url(#grid-d)" />
            </svg>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
            <div class="max-w-3xl">
                <!-- Mobile hero image - Responsive with aspect-ratio -->
                <div class="lg:hidden mb-4 -mx-4 sm:-mx-6 lg:-mx-8">
                    <img src="/images/home/feature-lineworker-ppe.jpg" alt="Aktivitas {{ config('app.name', 'Najkaltare') }} di Kecamatan {{ $district['name'] }}" width="1600" height="900" loading="eager" fetchpriority="high" decoding="async" class="hero-image-mobile w-full object-cover object-center rounded-xl" />
                </div>
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-burnt-brick border border-burnt-brick/80 text-white text-xs sm:text-sm font-semibold mb-5 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-safety-orange animate-pulse"></span>
                    Halaman Referensi Wilayah
                </div>
                <h1 id="dist-hero-heading" class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white leading-tight uppercase">
                    Skema Kompetensi {{ config('app.name', 'Najkaltare') }} – <span class="text-safety-orange">Kec. {{ $district['name'] }}</span>
                </h1>
                <p class="mt-4 text-base text-neutral-200 leading-relaxed">
                    Halaman ini adalah halaman referensi wilayah untuk <strong class="text-white">Kecamatan {{ $district['name'] }}</strong>, <a href="{{ route('regional.city', $district['city_slug']) }}" class="text-safety-orange hover:underline font-semibold">{{ $district['city_name'] }}</a>, Provinsi {{ $district['province'] }}.
                    Untuk konsultasi skema kompetensi {{ config('app.name', 'Najkaltare') }}, hubungi kami melalui WhatsApp.
                </p>
                <div class="mt-5 w-full sm:w-auto">
                    <x-wa-cta variant="primary" size="md" :message="$waMessage" label="Konsultasi via WhatsApp" class="w-full bg-safety-orange hover:bg-burnt-brick text-industry-black hover:text-white font-bold" />
                </div>
            </div>
        </div>
    </section>

    {{-- Thin-content notice --}}
    <section class="py-8 bg-neutral-50 border-b border-neutral-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-start gap-4">
                <svg class="w-5 h-5 text-burnt-brick shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-dark-mahogany leading-relaxed">
                    Halaman ini menyediakan informasi awal tingkat kecamatan. Penyelenggaraan pelatihan tatap muka dan in-house training dikonfirmasi langsung melalui konsultasi WhatsApp.
                </p>
            </div>
        </div>
    </section>

    {{-- Program Links --}}
    <section class="py-14 sm:py-20 bg-white border-b border-neutral-200" aria-labelledby="dist-program-heading">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 id="dist-program-heading" class="font-extrabold text-industry-black mb-6 text-[clamp(1.25rem,4vw,1.5rem)] sm:text-[clamp(1.5rem,4.5vw,1.75rem)]">Skema Kompetensi {{ config('app.name', 'Najkaltare') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($programs as $prog)
                @php $extra = $loop->iteration > 6; @endphp
                <a href="{{ route('city.program.detail', ['city' => $district['city_slug'], 'program' => $prog['slug']]) }}" class="group flex flex-col bg-white rounded-xl border border-neutral-200 hover:border-burnt-brick hover:shadow-md transition-all overflow-hidden focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-burnt-brick {{ $extra ? 'scheme-extra' : '' }}" @if($extra) hidden @endif>
                    <div class="h-1.5 bg-safety-orange"></div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="text-xs font-bold uppercase tracking-wider text-burnt-brick mb-1">{{ $prog['category_label'] ?? 'Layanan' }}</div>
                        <h3 class="text-base font-bold text-industry-black mb-2 group-hover:text-burnt-brick transition-colors">{{ $prog['title'] }}</h3>
                        <p class="text-xs text-dark-mahogany flex-1 leading-relaxed line-clamp-3">{{ $prog['hero_subheading'] }}</p>
                        <div class="mt-4 pt-4 border-t border-neutral-100 text-xs font-bold text-burnt-brick flex items-center justify-between">
                            <span>Detail Layanan</span>
                            <span>&rarr;</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

            <div class="mt-10 text-center">
                <button type="button" class="js-show-all inline-flex items-center justify-center px-6 py-3 text-sm font-bold text-white bg-burnt-brick hover:bg-leaf rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-burnt-brick transition-colors" aria-expanded="false" data-extra-count="{{ max(0, count($programs) - 6) }}">
                    Lihat Semua Skema
                </button>
                <p class="mt-3 text-xs text-dark-mahogany">
                    Menampilkan 6 dari {{ count($programs) }} skema. Saat diperluas, seluruh {{ count($programs) }} skema tampil; pilih tombol yang sama untuk menampilkan lebih sedikit.
                </p>
            </div>
        </div>
    </section>

    {{-- Sibling Districts --}}
    @if(isset($district['sibling_districts']) && count($district['sibling_districts']) > 0)
    <section class="py-10 bg-neutral-50 border-b border-neutral-200" aria-labelledby="dist-sibling-heading">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 id="dist-sibling-heading" class="font-bold text-industry-black mb-4 text-[clamp(1rem,3.5vw,1.125rem)] sm:text-[clamp(1.125rem,4vw,1.25rem)]">
                Kecamatan Lain di {{ $district['city_name'] }}
            </h2>
            <div class="flex flex-wrap gap-2">
                @foreach(array_slice($district['sibling_districts'], 0, 12) as $sib)
                <a href="{{ route('regional.district', [$district['city_slug'], $sib['slug']]) }}" class="inline-flex px-3 py-1.5 rounded-full bg-white border border-neutral-200 hover:border-burnt-brick text-sm text-dark-mahogany hover:text-burnt-brick transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-burnt-brick">
                    {{ $sib['name'] }}
                </a>
                @endforeach
                <a href="{{ route('regional.city', $district['city_slug']) }}" class="inline-flex px-3 py-1.5 rounded-full bg-white border border-burnt-brick text-sm font-semibold text-burnt-brick hover:bg-neutral-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-burnt-brick">
                    Lihat semua kecamatan {{ $district['city_name'] }} &rarr;
                </a>
            </div>
        </div>
    </section>
    @endif

    <x-regional-map :citySlug="$district['city_slug']" :districtSlug="$district['slug']" :regionName="'Kecamatan ' . $district['name']" />

    {{-- Articles --}}
    @if(isset($articles) && count($articles) > 0)
    <section id="artikel" class="py-14 sm:py-20 bg-white border-b border-neutral-200" aria-labelledby="dist-article-heading">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 id="dist-article-heading" class="text-xl font-extrabold text-industry-black mb-2">
                Artikel &amp; Panduan di Kecamatan {{ $district['name'] }}
            </h2>
            <p class="text-sm text-dark-mahogany mb-8">Kumpulan artikel edukatif terkait skema kompetensi dan layanan {{ config('app.name', 'Najkaltare') }} untuk wilayah {{ $district['city_name'] }}.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($articles as $art)
                <a href="{{ route('articles.show', $art->slug) }}" class="group flex flex-col bg-white rounded-xl border border-neutral-200 hover:border-burnt-brick hover:shadow-md transition-all overflow-hidden focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-burnt-brick">
                    {{-- Featured Image --}}
                    @if($art->featured_image)
                        <div class="relative overflow-hidden bg-neutral-100 aspect-video max-h-[240px]">
                            <img
                                src="/{{ ltrim($art->featured_image, '/') }}"
                                alt="{{ $art->title }}"
                                class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-300"
                                loading="lazy"
                            >
                        </div>
                    @else
                        <div class="aspect-video bg-gradient-to-br from-neutral-100 to-neutral-200 flex items-center justify-center">
                            <svg class="w-10 h-10 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                    @endif
                    <div class="p-6 flex-1 flex flex-col">
                        <h3 class="text-base font-bold text-industry-black mb-2 group-hover:text-burnt-brick transition-colors line-clamp-2">{{ $art->title }}</h3>
                        <p class="text-xs text-dark-mahogany flex-1 leading-relaxed line-clamp-3">{{ $art->excerpt }}</p>
                        <div class="mt-4 pt-4 border-t border-neutral-100 text-xs font-bold text-burnt-brick flex items-center justify-between">
                            <span>Baca Selengkapnya</span>
                            <span>&rarr;</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Final CTA --}}
    <section class="py-14 sm:py-20 bg-industry-black text-white" aria-labelledby="dist-cta-heading">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="max-w-2xl mx-auto">
                <h2 id="dist-cta-heading" class="text-xl sm:text-2xl font-extrabold uppercase tracking-tight">
                    Konsultasi via WhatsApp<br><span class="text-safety-orange">Kecamatan {{ $district['name'] }}</span>
                </h2>
                <p class="mt-4 text-sm text-neutral-200 leading-relaxed">
                    Hubungi kami untuk informasi skema kompetensi, halaman program, atau kebutuhan konsultasi {{ config('app.name', 'Najkaltare') }}.
                </p>
                <div class="mt-5 flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 w-full">
                    <x-wa-cta variant="primary" size="lg" :message="$waMessage" label="Konsultasi Sekarang" class="w-full sm:w-auto bg-safety-orange hover:bg-burnt-brick text-industry-black hover:text-white font-bold shadow-lg" />
                    <x-button :href="route('regional.city', $district['city_slug'])" variant="outline" size="lg" class="w-full sm:w-auto text-white border-2 border-neutral-400 hover:bg-neutral-800">{{ $district['city_name'] }}</x-button>
                </div>
            </div>
        </div>
    </section>
@endsection
