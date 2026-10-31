@extends('layouts.app')

@section('title', $program['title'] . ($region ? ' — ' . $region['label'] : ''))
@section('meta_description', $pageContent['lede'])
@section('wa_message', $waMessage)

@php
    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => collect($pageContent['faqs'])->map(fn ($faq) => [
            '@type' => 'Question', 'name' => $faq['question'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
        ])->values()->all(),
    ];
@endphp

@section('content')
    <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <div class="bg-neutral-100 border-b border-neutral-200"><x-breadcrumb :items="$breadcrumbs" /></div>

    <section class="relative overflow-hidden bg-industry-black text-white border-b border-dark-mahogany" aria-labelledby="training-title">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-11 sm:py-16 lg:py-20">
            <div class="max-w-4xl">
                <span class="inline-flex rounded-full border border-safety-orange/60 bg-safety-orange/10 px-3 py-1.5 text-xs sm:text-sm font-semibold text-safety-orange">{{ $program['category_label'] }}</span>
                <p class="mt-5 text-sm font-medium text-neutral-300">Pelatihan teknis @if($region) · {{ $region['label'] }} @endif</p>
                <h1 id="training-title" class="mt-2 text-[clamp(2rem,6vw,4rem)] leading-[1.08] tracking-tight font-extrabold">{{ $program['title'] }}</h1>
                <p class="mt-5 max-w-3xl text-base sm:text-lg leading-relaxed text-neutral-200">{{ $pageContent['lede'] }}</p>
                <div class="mt-7 flex flex-col sm:flex-row gap-3">
                    <x-wa-cta variant="primary" size="lg" :message="$waMessage" label="Konsultasikan Pelatihan" class="w-full sm:w-auto bg-safety-orange text-industry-black font-bold" />
                    <x-button href="#informasi-resmi" variant="outline" size="lg" class="w-full sm:w-auto text-white border-neutral-400">Lihat informasi program</x-button>
                </div>
            </div>
            <dl class="mt-10 grid grid-cols-2 lg:grid-cols-4 gap-px overflow-hidden rounded-xl border border-neutral-700 bg-neutral-700">
                <div class="bg-neutral-900 p-4 sm:p-5"><dt class="text-xs uppercase tracking-wide text-neutral-400">Kategori</dt><dd class="mt-1 font-semibold">{{ $program['category_label'] }}</dd></div>
                <div class="bg-neutral-900 p-4 sm:p-5"><dt class="text-xs uppercase tracking-wide text-neutral-400">Sektor terkait</dt><dd class="mt-1 font-semibold">{{ $pageContent['related_sectors'] }}</dd></div>
                <div class="bg-neutral-900 p-4 sm:p-5"><dt class="text-xs uppercase tracking-wide text-neutral-400">Jenis program</dt><dd class="mt-1 font-semibold">Pelatihan dalam katalog</dd></div>
                <div class="bg-neutral-900 p-4 sm:p-5"><dt class="text-xs uppercase tracking-wide text-neutral-400">Penyelenggara</dt><dd class="mt-1 font-semibold">Tidak dicantumkan · konfirmasi</dd></div>
            </dl>
        </div>
    </section>

    <section id="pengenalan" class="py-12 sm:py-16 bg-white border-b border-neutral-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-9 lg:gap-14 items-start">
            <div class="lg:col-span-7">
                <img src="{{ $pageContent['image']['path'] }}" alt="{{ $pageContent['image']['alt'] }}" width="900" height="520" loading="lazy" decoding="async" class="w-full aspect-[16/9] object-cover rounded-2xl border border-neutral-200">
                <p class="mt-3 text-xs text-neutral-500">Ilustrasi bidang {{ $program['category_label'] }}; bukan dokumentasi penyelenggaraan program ini.</p>
                <p class="mt-7 text-xs font-bold uppercase tracking-widest text-burnt-brick">Tujuan &amp; Ruang Lingkup</p>
                <h2 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-industry-black">Memahami konteks {{ $program['title'] }}</h2>
                <p class="mt-4 text-base sm:text-lg leading-relaxed text-dark-mahogany">{{ $pageContent['overview'] }}</p>
                <p class="mt-4 leading-relaxed text-dark-mahogany">{{ $pageContent['purpose'] }}</p>
                @if($pageContent['compliance_note'])
                    <aside class="mt-6 rounded-r-xl border-l-4 border-safety-orange bg-neutral-50 p-5 text-sm leading-relaxed text-dark-mahogany"><strong class="block text-industry-black">Catatan verifikasi</strong>{{ $pageContent['compliance_note'] }}</aside>
                @endif
            </div>
            <aside class="lg:col-span-5 rounded-2xl bg-neutral-50 border border-neutral-200 p-6 sm:p-8">
                <p class="text-xs font-bold uppercase tracking-widest text-burnt-brick">Relevansi profesional</p>
                <h3 class="mt-2 text-xl font-bold text-industry-black">Siapa yang mungkin memerlukan topik ini?</h3>
                <p class="mt-4 leading-relaxed text-dark-mahogany">{{ $pageContent['audience_note'] }}</p>
                <p class="mt-4 text-sm leading-relaxed text-neutral-600">Keterangan ini menunjukkan relevansi umum berdasarkan judul, bukan persyaratan resmi atau penetapan kelayakan peserta.</p>
                <div class="mt-6 border-t border-neutral-200 pt-5"><x-wa-cta variant="secondary" size="sm" :message="$waMessage" label="Tanyakan kesesuaian kebutuhan" class="w-full justify-center" /></div>
            </aside>
        </div>
    </section>

    @if($region)
    <section class="py-10 sm:py-14 bg-neutral-50 border-b border-neutral-200" aria-labelledby="regional-relevance">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-8">
            <div class="lg:col-span-8"><p class="text-xs font-bold uppercase tracking-widest text-burnt-brick">Konteks wilayah</p><h2 id="regional-relevance" class="mt-2 text-2xl font-bold text-industry-black">Relevansi topik di {{ $region['label'] }}</h2><p class="mt-4 leading-relaxed text-dark-mahogany">{{ $pageContent['regional_relevance'] }}</p><p class="mt-3 text-sm text-neutral-600">{{ $pageContent['regional_data_note'] }}</p></div>
            <nav class="lg:col-span-4" aria-label="Navigasi wilayah terkait"><h3 class="font-bold text-industry-black">Jelajahi wilayah</h3><ul class="mt-3 space-y-2">@foreach($regionalLinks as $link)<li><a class="text-burnt-brick underline underline-offset-4 hover:text-industry-black focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2" href="{{ $link['url'] }}">{{ $link['label'] }} <span aria-hidden="true">→</span></a></li>@endforeach</ul></nav>
        </div>
    </section>
    @endif

    <section id="kompetensi" class="py-12 sm:py-16 bg-neutral-50 border-b border-neutral-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl"><p class="text-xs font-bold uppercase tracking-widest text-burnt-brick">Penerapan teknis</p><h2 class="mt-2 text-2xl sm:text-3xl font-extrabold text-industry-black">Kompetensi &amp; Output Kerja</h2><p class="mt-4 leading-relaxed text-dark-mahogany">Area berikut membantu memetakan hubungan topik dengan pekerjaan. Ini bukan unit kompetensi atau keluaran resmi yang dijanjikan program.</p></div>
            <div class="mt-8 grid md:grid-cols-3 gap-4">@foreach($pageContent['competency_areas'] as $i => $item)<article class="bg-white border border-neutral-200 rounded-xl p-5 sm:p-6"><span class="text-sm font-bold text-burnt-brick">0{{ $i + 1 }}</span><h3 class="mt-3 font-bold text-industry-black">{{ $item['title'] }}</h3><p class="mt-2 text-sm leading-relaxed text-dark-mahogany">{{ $item['description'] }}</p></article>@endforeach</div>
        </div>
    </section>

    <section id="informasi-resmi" class="py-12 sm:py-16 bg-white border-b border-neutral-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-8 items-start">
            <div class="lg:col-span-5"><p class="text-xs font-bold uppercase tracking-widest text-burnt-brick">Informasi program</p><h2 class="mt-2 text-2xl sm:text-3xl font-extrabold text-industry-black">Informasi Penyelenggaraan &amp; Pelaksanaan</h2><p class="mt-4 leading-relaxed text-dark-mahogany">Katalog sumber memuat judul dan kategori. Rincian operasional yang tidak tersedia ditandai agar dapat dikonfirmasi sebelum mengambil keputusan.</p><p class="mt-3 text-xs leading-relaxed text-neutral-500">Atribusi judul: list_pelatihan_ayana.json. Sumber ini tidak menyertakan rincian penyelenggaraan atau silabus.</p></div>
            <dl class="lg:col-span-7 divide-y divide-neutral-200 rounded-xl border border-neutral-200 bg-white">@foreach($program['training_info'] as $info)<div class="sm:flex sm:items-start sm:justify-between gap-6 p-4 sm:p-5"><dt class="font-bold text-industry-black">{{ $info['label'] }}</dt><dd class="mt-1 sm:mt-0 sm:max-w-[68%] text-sm leading-relaxed text-dark-mahogany"><span class="font-semibold text-burnt-brick">{{ $info['status'] }}.</span> {{ $info['detail'] }}</dd></div>@endforeach</dl>
        </div>
    </section>

    <section id="materi" class="py-12 sm:py-16 bg-neutral-50 border-b border-neutral-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-8 items-start">
            <div class="lg:col-span-7"><p class="text-xs font-bold uppercase tracking-widest text-burnt-brick">Persiapan kebutuhan</p><h2 class="mt-2 text-2xl sm:text-3xl font-extrabold text-industry-black">Pokok Materi &amp; Tahapan Kerja</h2><p class="mt-4 leading-relaxed text-dark-mahogany">Topik umum berangkat dari judul “{{ $program['title'] }}” dan bidang {{ $program['category_label'] }}. Katalog tidak menyediakan silabus; tahap di samping merupakan kerangka konsultasi, bukan kurikulum atau urutan pembelajaran resmi.</p><p class="mt-4 leading-relaxed text-dark-mahogany">Outline, metode, perangkat, dan kedalaman materi perlu diminta langsung dan dikonfirmasi kepada penyelenggara.</p></div>
            <ol class="lg:col-span-5 space-y-3">@foreach($pageContent['consultation_stages'] as $i => $stage)<li class="flex gap-4 rounded-xl border border-neutral-200 bg-white p-4"><span class="grid size-8 shrink-0 place-items-center rounded-full bg-industry-black text-safety-orange font-bold">{{ $i + 1 }}</span><div><h3 class="font-bold text-industry-black">{{ $stage['title'] }}</h3><p class="mt-1 text-sm leading-relaxed text-dark-mahogany">{{ $stage['detail'] }}</p></div></li>@endforeach</ol>
            <div class="lg:col-span-12 rounded-xl bg-industry-black p-5 sm:p-7 text-white sm:flex sm:items-center sm:justify-between gap-6"><div><h3 class="text-lg font-bold">Perlu outline atau proposal?</h3><p class="mt-1 text-sm text-neutral-300">Sampaikan topik dan konteks kebutuhan untuk dibahas.</p></div><x-wa-cta variant="primary" size="md" :message="$waMessage" label="Minta informasi dan proposal" class="mt-4 sm:mt-0 bg-safety-orange text-industry-black font-bold" /></div>
        </div>
    </section>

    @if(count($relatedPrograms))
    <section id="program-lain" class="py-12 sm:py-16 bg-white border-b border-neutral-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"><p class="text-xs font-bold uppercase tracking-widest text-burnt-brick">Jelajahi katalog</p><h2 class="mt-2 text-2xl sm:text-3xl font-extrabold text-industry-black">Layanan Terkait</h2><p class="mt-3 max-w-2xl text-dark-mahogany">Pelatihan yang berdekatan berdasarkan kategori atau istilah topik pada katalog.</p>
            <div class="mt-7 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">@foreach($relatedPrograms as $other)<article class="flex flex-col border border-neutral-200 rounded-xl p-5 hover:border-burnt-brick transition-colors"><span class="text-xs font-semibold text-burnt-brick">{{ $other['category_label'] }}</span><h3 class="mt-2 font-bold leading-snug text-industry-black">{{ $other['title'] }}</h3><a class="mt-auto pt-5 font-semibold text-sm text-burnt-brick underline underline-offset-4" href="{{ $region ? route($detailRoute, array_merge($detailRouteParams, ['program' => $other['slug']])) : route('program.show', $other['slug']) }}">Lihat detail <span aria-hidden="true">→</span></a></article>@endforeach</div>
        </div>
    </section>
    @endif

    <section id="faq" class="py-12 sm:py-16 bg-neutral-50 border-b border-neutral-200" aria-labelledby="faq-heading"><div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8"><p class="text-xs font-bold uppercase tracking-widest text-burnt-brick">Tanya jawab</p><h2 id="faq-heading" class="mt-2 text-2xl sm:text-3xl font-extrabold text-industry-black">FAQ {{ $program['title'] }}</h2><div class="mt-7 space-y-3">@foreach($pageContent['faqs'] as $faq)<details class="group rounded-xl border border-neutral-200 bg-white p-5 open:border-burnt-brick"><summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-bold text-industry-black focus-visible:outline focus-visible:outline-2 focus-visible:outline-safety-orange"><span>{{ $faq['question'] }}</span><span class="text-burnt-brick transition-transform group-open:rotate-180" aria-hidden="true">⌄</span></summary><p class="mt-4 border-t border-neutral-200 pt-4 text-sm sm:text-base leading-relaxed text-dark-mahogany">{{ $faq['answer'] }}</p></details>@endforeach</div></div></section>

    <section id="konsultasi" class="bg-industry-black py-12 sm:py-16 text-white"><div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 sm:flex sm:items-center sm:justify-between gap-8"><div><p class="text-xs font-bold uppercase tracking-widest text-safety-orange">Konsultasi Chifheto</p><h2 class="mt-2 text-2xl sm:text-3xl font-extrabold">Diskusikan kebutuhan {{ $program['title'] }}</h2><p class="mt-3 max-w-2xl text-neutral-300 leading-relaxed">Informasi jadwal, biaya, persyaratan, cakupan, dan ketersediaan perlu dikonfirmasi langsung.</p></div><x-wa-cta variant="primary" size="lg" :message="$waMessage" label="Konsultasi melalui WhatsApp" class="mt-5 sm:mt-0 shrink-0 bg-safety-orange text-industry-black font-bold" /></div></section>
@endsection
