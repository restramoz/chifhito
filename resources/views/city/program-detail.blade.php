@extends('layouts.app')

@section('title', $program['title'] . ' — ' . $city['name'])
@section('meta_description', 'Informasi dan konsultasi ' . $program['title'] . ' untuk mitra ' . config('app.name', 'Chifheto') . ', fasilitas produksi, dan tenaga profesional di ' . $city['name'] . ', Provinsi ' . $city['province'] . '. Rincian skema, unit kompetensi, dan persyaratan penyelenggaraan.')
@section('wa_message', $waMessage)

@section('content')
    <!-- BREADCRUMB NAVIGATION -->
    <div class="bg-neutral-100 border-b border-neutral-200">
        <x-breadcrumb :items="[
            ['label' => 'Wilayah', 'url' => null],
            ['label' => $city['province'], 'url' => route('regional.province', $city['province_slug'])],
            ['label' => $city['name'], 'url' => route('regional.city', $city['slug'])],
            ['label' => $program['title'], 'url' => null],
        ]" />
    </div>

    <!-- HERO SECTION (DARK SURFACE) -->
    <section class="relative bg-industry-black text-white overflow-hidden border-b border-dark-mahogany" aria-labelledby="program-city-hero-heading">
        <!-- Industrial Grid Pattern Accent -->
        <div class="absolute inset-0 opacity-10 pointer-events-none" aria-hidden="true">
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="program-city-grid-pattern" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#FFFFFF" stroke-width="1" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#program-city-grid-pattern)" />
            </svg>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24">
            <div class="max-w-3xl">
                <!-- Program Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-burnt-brick border border-burnt-brick/80 text-white text-xs sm:text-sm font-semibold mb-6 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-safety-orange animate-pulse"></span>
                    <span>{{ $program['badge'] }}</span>
                </div>

                <!-- Main Heading with Regional Scope -->
                <h1 id="program-city-hero-heading" class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-tight uppercase">
                    {{ $program['hero_heading'] }} <span class="text-safety-orange">{{ $program['hero_highlight'] }}</span><br>
                    <span class="text-2xl sm:text-3xl lg:text-4xl text-neutral-100 font-bold tracking-normal block mt-1">{{ $city['name'] }}</span>
                </h1>

                <!-- Subheading -->
                <p class="mt-6 text-base sm:text-lg lg:text-xl text-neutral-200 leading-relaxed">
                    {{ $program['hero_subheading'] }}
                </p>

                <!-- Action Buttons -->
                <div class="mt-8 sm:mt-10 flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                    <x-wa-cta variant="primary" size="lg" :message="$waMessage" :label="'Konsultasi ' . explode('(', $program['title'])[0]" class="shadow-md bg-safety-orange hover:bg-burnt-brick text-industry-black hover:text-white font-bold" />
                    <x-button href="#informasi-resmi" variant="outline" size="lg" class="text-white border-2 border-neutral-400 hover:bg-neutral-800">
                        Rincian &amp; Persyaratan
                    </x-button>
                </div>

                <!-- Key Attributes Bar -->
                <div class="mt-12 pt-8 border-t border-neutral-800 grid grid-cols-2 sm:grid-cols-4 gap-6">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-neutral-400">Kategori</div>
                        <div class="text-lg sm:text-xl font-extrabold text-safety-orange mt-0.5 truncate">{{ $program['level'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-neutral-400">Wilayah</div>
                        <div class="text-base sm:text-lg font-extrabold text-white mt-0.5 truncate">{{ $city['name'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-neutral-400">Sektor</div>
                        <div class="text-lg sm:text-xl font-extrabold text-safety-orange mt-0.5">{{ config('app.name', 'Chifheto') }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-neutral-400">Sifat Program</div>
                        <div class="text-lg sm:text-xl font-extrabold text-white mt-0.5">Skema Kompetensi</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 1: PENGENALAN & SASARAN PESERTA (LIGHT SURFACE) -->
    <section id="pengenalan" class="py-14 sm:py-20 bg-white border-b border-neutral-200" aria-labelledby="pengenalan-heading">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">
                <!-- Left: Intro Narrative -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center gap-2">
                        <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-burnt-brick bg-neutral-100 border border-burnt-brick/20 px-3 py-1 rounded-full">
                            Tujuan &amp; Ruang Lingkup
                        </span>
                    </div>
                    <h2 id="pengenalan-heading" class="text-2xl sm:text-3xl font-extrabold text-industry-black tracking-tight">
                        Mengenal Lebih Dekat {{ $program['title'] }} di {{ $city['name'] }}
                    </h2>
                    <p class="text-base sm:text-lg text-dark-mahogany leading-relaxed">
                        {{ $program['introduction'] }}
                    </p>
                    <div class="p-5 rounded-xl bg-neutral-50 border border-neutral-200 shadow-sm">
                        <div class="flex items-start gap-3.5">
                            <div class="w-9 h-9 rounded-lg bg-burnt-brick/10 text-burnt-brick flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div class="text-sm text-dark-mahogany leading-relaxed">
                                <span class="font-bold text-industry-black block mb-0.5">Fokus Keselamatan Kerja {{ config('app.name', 'Chifheto') }}</span>
                                Program diarahkan pada pemahaman skema kompetensi, praktik kerja yang baik, dan pemeliharaan keselamatan kerja pada kegiatan operasional di wilayah {{ $city['name'] }} dan sekitarnya.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Target Audience Card -->
                <div class="lg:col-span-5">
                    <div class="bg-neutral-50 rounded-2xl border border-neutral-200 p-6 sm:p-8 shadow-sm">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-lg bg-burnt-brick/10 text-burnt-brick flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-industry-black">Sasaran Peserta / Pengguna Layanan</h3>
                                <p class="text-xs text-neutral-600">Profil yang relevan untuk layanan ini</p>
                            </div>
                        </div>

                        <ul class="space-y-3.5" role="list">
                            @foreach ($program['target_audience'] as $audience)
                                <li class="flex items-start gap-3 text-sm text-dark-mahogany">
                                    <svg class="w-5 h-5 text-burnt-brick shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>{{ $audience }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-6 pt-6 border-t border-neutral-200">
                            <x-wa-cta variant="secondary" size="sm" :message="$waMessage" label="Konsultasikan Kualifikasi Anda" class="w-full justify-center" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 2: PERAN & TANGGUNG JAWAB LAPANGAN (LIGHT SURFACE) -->
    <section id="tanggung-jawab" class="py-14 sm:py-20 bg-neutral-50 border-b border-neutral-200" aria-labelledby="tanggung-jawab-heading">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mb-12">
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-burnt-brick bg-white border border-burnt-brick/20 px-3 py-1 rounded-full shadow-xs">
                    Aplikasi Lapangan
                </span>
                <h2 id="tanggung-jawab-heading" class="mt-3 text-2xl sm:text-3xl font-extrabold text-industry-black tracking-tight">
                    Peran &amp; Tanggung Jawab di {{ $city['name'] }}
                </h2>
                <p class="mt-4 text-base text-dark-mahogany leading-relaxed">
                    Berikut adalah gambaran ruang lingkup tugas dan tanggung jawab pengelolaan yang umumnya diemban oleh penanggung jawab teknis atau operasional pada layanan {{ $program['title'] }}.
                </p>
            </div>

            <!-- Responsibilities Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($program['roles_and_responsibilities'] as $index => $item)
                    <div class="flex flex-col bg-white rounded-xl border border-neutral-200 p-6 shadow-sm hover:shadow-md transition">
                        <div class="w-8 h-8 rounded-lg bg-burnt-brick/10 text-burnt-brick flex items-center justify-center font-bold text-sm mb-4">
                            {{ $index + 1 }}
                        </div>
                        <h3 class="text-base font-bold text-industry-black mb-2">
                            {{ $item['title'] }}
                        </h3>
                        <p class="text-sm text-dark-mahogany leading-relaxed flex-1">
                            {{ $item['description'] }}
                        </p>
                    </div>
                @endforeach
            </div>

            <!-- Cautionary Note -->
            <div class="mt-10 p-6 rounded-2xl bg-white border border-neutral-200 shadow-sm text-dark-mahogany">
                <div class="flex items-start gap-3.5">
                    <svg class="w-6 h-6 text-burnt-brick shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="text-sm space-y-1">
                        <h4 class="font-bold text-industry-black">Perhatian: Pembedaan Praktik Kerja Lapangan &amp; Persyaratan Sertifikasi / Dokumen</h4>
                        <p class="text-dark-mahogany leading-relaxed">
                            Uraian peran di atas merupakan ringkasan umum lingkup operasional pada sektor terkait. Persyaratan administratif formal ditentukan oleh skema layanan dan ketentuan yang berlaku. Calon peserta atau badan usaha dari {{ $city['name'] }} dianjurkan mengonfirmasikan berkas pribadinya kepada konsultan kami sebelum proses pendaftaran.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 3: INFORMASI PELATIHAN & TRANSPARANSI DATA (LIGHT SURFACE) -->
    <section id="informasi-resmi" class="py-14 sm:py-20 bg-white border-b border-neutral-200" aria-labelledby="informasi-heading">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mb-12">
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-burnt-brick bg-neutral-100 border border-burnt-brick/20 px-3 py-1 rounded-full">
                    Transparansi Program
                </span>
                <h2 id="informasi-heading" class="mt-3 text-2xl sm:text-3xl font-extrabold text-industry-black tracking-tight">
                    Informasi Penyelenggaraan di {{ $city['name'] }}
                </h2>
                <p class="mt-4 text-base text-dark-mahogany leading-relaxed">
                    Kami berkomitmen menyajikan informasi faktual tanpa mengklaim jadwal fiktif atau biaya tanpa konfirmasi resmi. Seluruh rincian diverifikasi langsung melalui tim konsultan kami untuk klien di {{ $city['name'] }}, Provinsi {{ $city['province'] }}.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Left: Verified Parameters Grid -->
                <div class="lg:col-span-7 space-y-4">
                    <div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden shadow-sm divide-y divide-neutral-200">
                        @foreach ($program['training_info'] as $key => $info)
                            <div class="p-5 sm:p-6 flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                <div class="sm:w-1/3">
                                    <span class="text-xs font-bold uppercase tracking-wider text-industry-black block mb-1">
                                        {{ $info['label'] }}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-neutral-100 text-dark-mahogany border border-neutral-200">
                                        {{ $info['status'] }}
                                    </span>
                                </div>
                                <div class="sm:w-2/3">
                                    <p class="text-sm text-dark-mahogany leading-relaxed">
                                        {{ $info['detail'] }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Transparency Disclaimer Card -->
                    <div class="p-5 rounded-xl bg-neutral-50 border border-neutral-200 text-xs text-dark-mahogany leading-relaxed space-y-2">
                        <div class="flex items-center gap-2 font-bold text-industry-black text-sm">
                            <svg class="w-4 h-4 text-burnt-brick" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Komitmen Keterbukaan Informasi Kami</span>
                        </div>
                        <p>
                            Kami tidak menampilkan estimasi biaya atau sertifikasi palsu yang belum diverifikasi secara resmi. Silakan hubungi kami untuk mendapatkan proposal penawaran tertulis lengkap dan silabus resmi sesuai kebutuhan industri Anda.
                        </p>
                    </div>
                </div>

                <!-- Right: Competency Topics Preview & CTA Card -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-neutral-50 rounded-2xl border border-neutral-200 p-6 sm:p-8 shadow-sm">
                        <h3 class="text-lg font-bold text-industry-black mb-2">
                            Cakupan Topik / Deliverables Inti
                        </h3>
                        <p class="text-xs text-neutral-600 mb-5 leading-relaxed">
                            Materi dan deliverables mengacu pada struktur skema kompetensi yang tersedia:
                        </p>

                        @if (!empty($program['curriculum_topics']))
                            <ul class="space-y-3 mb-6" role="list">
                                @foreach ($program['curriculum_topics'] as $topic)
                                    <li class="flex items-start gap-2.5 text-xs sm:text-sm text-dark-mahogany">
                                        <svg class="w-4 h-4 text-burnt-brick shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                        <span>{{ $topic }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mb-6 text-xs sm:text-sm text-dark-mahogany leading-relaxed">
                                Materi detail ditetapkan berdasarkan kebutuhan pelatihan yang dipilih. Rincian topik, durasi, dan modul dikonfirmasi saat konsultasi.
                            </p>
                        @endif

                        <div class="pt-5 border-t border-neutral-200">
                            <x-wa-cta variant="primary" size="md" :message="$waMessage" label="Minta Silabus / Rincian via WhatsApp" class="w-full justify-center bg-safety-orange hover:bg-burnt-brick text-industry-black hover:text-white font-bold" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 4: SKEMA LAINNYA DI KOTA INI (LIGHT SURFACE) -->
    @if(isset($otherPrograms) && count($otherPrograms) > 0)
    <section id="program-lain" class="py-14 sm:py-20 bg-neutral-50 border-b border-neutral-200" aria-labelledby="program-lain-heading">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-burnt-brick bg-white border border-burnt-brick/20 px-3 py-1 rounded-full shadow-xs">
                    Pilihan Layanan Lain
                </span>
                <h2 id="program-lain-heading" class="mt-3 text-2xl sm:text-3xl font-extrabold text-industry-black tracking-tight">
                    Skema Kompetensi Lainnya di {{ $city['name'] }}
                </h2>
                <p class="mt-4 text-base text-dark-mahogany">
                    Bandingkan skema kompetensi lainnya untuk menemukan program yang sesuai dengan kebutuhan tim Anda di {{ $city['name'] }}.
                </p>
            </div>

            <!-- Other Programs Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
                @foreach ($otherPrograms as $other)
                    <div class="flex flex-col bg-white rounded-2xl border border-neutral-200 p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-neutral-100 border border-neutral-200 text-burnt-brick text-xs font-bold w-fit mb-4">
                            <span>{{ $other['badge'] ?? $other['level'] }}</span>
                        </div>
                        <h3 class="text-lg font-bold text-industry-black mb-2">
                            {{ $other['title'] }} — {{ $city['name'] }}
                        </h3>
                        <p class="text-xs sm:text-sm text-dark-mahogany leading-relaxed mb-6 flex-1 line-clamp-3">
                            {{ $other['hero_subheading'] }}
                        </p>
                        <div class="pt-4 border-t border-neutral-100">
                            <x-button :href="route('city.program.detail', ['city' => $city['slug'], 'program' => $other['slug']])" variant="secondary" size="sm" class="w-full justify-center">
                                Rincian Layanan &rarr;
                            </x-button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- SECTION 5: FAQ KHUSUS PROGRAM (LIGHT SURFACE) -->
    <section id="faq" class="py-14 sm:py-20 bg-white border-b border-neutral-200" aria-labelledby="faq-heading">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-burnt-brick bg-neutral-100 border border-burnt-brick/20 px-3 py-1 rounded-full">
                    Tanya Jawab
                </span>
                <h2 id="faq-heading" class="mt-3 text-2xl sm:text-3xl font-extrabold text-industry-black tracking-tight">
                    Pertanyaan Umum Seputar {{ $program['title'] }}
                </h2>
                <p class="mt-4 text-base text-dark-mahogany">
                    Jawaban lugas dan faktual untuk pertanyaan yang sering diajukan mengenai layanan ini bagi klien di {{ $city['name'] }}.
                </p>
            </div>

            <div class="space-y-4">
                @foreach ($program['faqs'] as $index => $faq)
                    <details class="group bg-white rounded-xl border border-neutral-200 p-5 sm:p-6 shadow-sm transition-all duration-150 open:border-burnt-brick">
                        <summary class="flex items-center justify-between gap-4 font-bold text-industry-black text-base sm:text-lg cursor-pointer list-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-safety-orange rounded">
                            <span>{{ $faq['question'] }}</span>
                            <span class="w-7 h-7 rounded-full bg-neutral-100 flex items-center justify-center shrink-0 group-open:rotate-180 transition-transform duration-200 text-neutral-600">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </summary>
                        <div class="mt-4 pt-4 border-t border-neutral-200 text-sm sm:text-base text-dark-mahogany leading-relaxed">
                            {{ $faq['answer'] }}
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- SECTION 6: FINAL CONVERSION & KONSULTASI (DARK SURFACE) -->
    <section id="konsultasi" class="py-16 sm:py-24 bg-industry-black text-white relative overflow-hidden" aria-labelledby="konsultasi-heading">
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="max-w-3xl mx-auto">
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-safety-orange bg-dark-mahogany border border-burnt-brick px-3.5 py-1.5 rounded-full inline-block mb-6">
                    Layanan Konsultasi Responsif
                </span>
                <h2 id="konsultasi-heading" class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight uppercase">
                    Konsultasikan <span class="text-safety-orange">{{ $program['title'] }}</span> di {{ $city['name'] }}
                </h2>
                <p class="mt-4 text-base sm:text-lg text-neutral-200 leading-relaxed">
                    Dapatkan kejelasan skema yang sesuai, pengecekan persyaratan administrasi, serta proposal resmi langsung melalui tim representasi kami di {{ $city['name'] }}.
                </p>

                <div class="mt-8 sm:mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <x-wa-cta variant="primary" size="lg" :message="$waMessage" :label="'Hubungi Konsultan ' . explode('(', $program['title'])[0]" class="w-full sm:w-auto shadow-lg bg-safety-orange hover:bg-burnt-brick text-industry-black hover:text-white font-bold" />
                    <x-button :href="route('regional.city', $city['slug'])" variant="outline" size="lg" class="w-full sm:w-auto text-white border-2 border-neutral-400 hover:bg-neutral-800">
                        Kembali ke {{ $city['name'] }}
                    </x-button>
                </div>

                <!-- Internal Links Row -->
                <div class="mt-12 pt-8 border-t border-neutral-800 flex flex-wrap items-center justify-center gap-6 text-xs text-neutral-400">
                    <a href="{{ route('home') }}" class="hover:text-safety-orange transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-safety-orange rounded">
                        Beranda
                    </a>
                    <span>•</span>
                    <a href="{{ route('regional.province', $city['province_slug']) }}" class="hover:text-safety-orange transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-safety-orange rounded">
                        Provinsi {{ $city['province'] }}
                    </a>
                    <span>•</span>
                    <a href="{{ route('regional.city', $city['slug']) }}" class="hover:text-safety-orange transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-safety-orange rounded">
                        {{ $city['name'] }}
                    </a>
                    <span>•</span>
                    <a href="{{ route('home') }}#faq" class="hover:text-safety-orange transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-safety-orange rounded">
                        FAQ Umum
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
