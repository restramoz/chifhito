@extends('layouts.app')

@section('title', $program['title'] . ' — ' . $region['label'])
@section('meta_description', 'Gambaran topik ' . $program['title'] . ' untuk kebutuhan di ' . $region['label'] . '. Tinjau konteks, wilayah, topik terkait, dan konfirmasikan ruang lingkup melalui konsultasi Chifheto.')
@section('wa_message', $waMessage)

@push('schema')
@php($faqSchema = [
    '@'.'context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => collect($pageContent['faqs'])->map(fn ($faq) => [
        '@type' => 'Question',
        'name' => $faq['question'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
    ])->values()->all(),
])
<script type="application/ld+json">{!! json_encode($faqSchema, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
<article class="ch-regional-training">
    <div class="ch-rt-hero">
        <div class="ch-rt-copy">
            <x-breadcrumb :items="$breadcrumbs" />
            <p class="ch-eyebrow">{{ $program['category_label'] }} <span aria-hidden="true">/</span> {{ $region['label'] }}</p>
            <h1>{{ $program['title'] }}</h1>
            <p class="ch-rt-lede">{{ $pageContent['lede'] }}</p>
            <div class="ch-rt-actions">
                <x-wa-cta variant="primary" size="lg" :message="$waMessage" label="Konsultasikan topik ini" class="ch-button" />
                <a class="ch-rt-secondary" href="#gambaran">Lihat gambaran topik <span aria-hidden="true">↓</span></a>
            </div>
            <p class="ch-rt-source">Judul katalog: <strong>{{ $program['title'] }}</strong> · Sumber: {{ $program['source_file'] }}</p>
        </div>
        <figure class="ch-rt-visual">
            <img src="{{ $pageContent['image']['path'] }}" alt="{{ $pageContent['image']['alt'] }}" width="960" height="720" loading="eager" fetchpriority="high">
            <figcaption><span>Bidang pelatihan</span><strong>{{ $program['category_label'] }}</strong></figcaption>
        </figure>
    </div>

    <section id="gambaran" class="ch-rt-overview" aria-labelledby="gambaran-title">
        <div class="ch-rt-section-label"><span>01</span><p>Gambaran topik</p></div>
        <div class="ch-rt-overview-copy">
            <h2 id="gambaran-title">Apa yang ditunjukkan oleh judul program ini?</h2>
            <p>{{ $pageContent['overview'] }}</p>
            <div class="ch-rt-note"><strong>Batas informasi katalog</strong><span>Sumber yang tersedia memuat judul program. Rincian silabus, praktik, durasi, biaya, persyaratan, dan sertifikasi tidak diasumsikan di halaman ini.</span></div>
        </div>
        <aside class="ch-rt-subject">
            <span>Pokok bahasan pada judul</span>
            <strong>{{ $program['title'] }}</strong>
            <small>{{ $program['category_label'] }}</small>
        </aside>
    </section>

    <section class="ch-rt-audience" aria-labelledby="audience-title">
        <div class="ch-rt-section-label"><span>02</span><p>Relevansi profesional</p></div>
        <div>
            <h2 id="audience-title">Siapa yang mungkin membutuhkan topik ini?</h2>
            <p>{{ $pageContent['audience_note'] }}</p>
            <p class="ch-rt-muted">Kecocokan peserta dan prasyarat resmi tidak dapat disimpulkan dari judul katalog; konfirmasikan berdasarkan pekerjaan, pengalaman, dan kebutuhan tim.</p>
        </div>
        <div class="ch-rt-audience-mark" aria-hidden="true">{{ str_pad((string) ($program['source_index'] + 1), 3, '0', STR_PAD_LEFT) }}</div>
    </section>

    <section class="ch-rt-region" aria-labelledby="region-title">
        <div class="ch-rt-region-heading">
            <div class="ch-rt-section-label"><span>03</span><p>Konteks wilayah</p></div>
            <h2 id="region-title">Menilai relevansi di {{ $region['label'] }}</h2>
        </div>
        <div class="ch-rt-region-body">
            <p>{{ $pageContent['regional_relevance'] }}</p>
            <div class="ch-rt-location"><span>Wilayah pada halaman</span><strong>{{ $region['label'] }}</strong>@if(isset($region['province']) && $region['type'] !== 'province')<small>Provinsi {{ $region['province']['name'] }}</small>@endif</div>
        </div>
    </section>

    <section class="ch-rt-related" aria-labelledby="related-title">
        <div class="ch-rt-related-heading">
            <div><div class="ch-rt-section-label"><span>04</span><p>Jelajahi topik lain</p></div><h2 id="related-title">Pelatihan terkait</h2></div>
            <a href="{{ route('catalog.index', ['category' => $program['category_label']]) }}">Lihat bidang {{ $program['category_label'] }} <span aria-hidden="true">↗</span></a>
        </div>
        <ul class="ch-rt-related-list">
            @foreach($relatedPrograms as $related)
                <li><a href="{{ route($detailRoute, array_merge($detailRouteParams, ['program' => $related['slug']])) }}"><span><small>{{ $related['category_label'] }}</small><strong>{{ $related['title'] }}</strong></span><span class="ch-rt-arrow" aria-hidden="true">↗</span></a></li>
            @endforeach
        </ul>
    </section>

    <section class="ch-rt-process" aria-labelledby="process-title">
        <div class="ch-rt-section-label"><span>05</span><p>Langkah konsultasi</p></div>
        <div class="ch-rt-process-intro"><h2 id="process-title">Mulai dari kebutuhan tim Anda.</h2><p>Informasi akhir tentang cakupan program dan penyelenggaraan dikonfirmasi bersama penyedia.</p></div>
        <ol>
            <li><span>01</span><div><strong>Kirim pertanyaan</strong><p>Sebutkan judul pelatihan dan kebutuhan awal tim.</p></div></li>
            <li><span>02</span><div><strong>Konfirmasi kebutuhan dan lokasi</strong><p>Jelaskan profil pekerjaan, jumlah peserta, dan wilayah yang dimaksud.</p></div></li>
            <li><span>03</span><div><strong>Bahas ruang lingkup</strong><p>Tanyakan materi, persyaratan, ketersediaan, dan hal yang perlu diverifikasi.</p></div></li>
            <li><span>04</span><div><strong>Konfirmasi pengaturan</strong><p>Rincian pelaksanaan dibahas lebih lanjut dengan penyelenggara.</p></div></li>
        </ol>
    </section>

    <section class="ch-rt-faq" aria-labelledby="faq-title">
        <div class="ch-rt-faq-intro"><div class="ch-rt-section-label"><span>06</span><p>Pertanyaan umum</p></div><h2 id="faq-title">Sebelum berkonsultasi</h2><p>Jawaban mengikuti informasi yang tersedia; rincian program dikonfirmasi langsung.</p></div>
        <div class="ch-rt-faq-list">
            @foreach($pageContent['faqs'] as $faq)
                <details><summary>{{ $faq['question'] }}<span aria-hidden="true">＋</span></summary><p>{{ $faq['answer'] }}</p></details>
            @endforeach
        </div>
    </section>

    <nav class="ch-rt-region-nav" aria-label="Jelajahi wilayah terkait">
        <div><span class="ch-eyebrow">Jelajahi wilayah</span><h2>Terhubung dengan area sekitar</h2></div>
        <ul>@foreach($regionalLinks as $link)<li><a href="{{ $link['url'] }}">{{ $link['label'] }} <span aria-hidden="true">↗</span></a></li>@endforeach</ul>
    </nav>

    <section class="ch-rt-cta" aria-labelledby="consult-title">
        <div><p class="ch-eyebrow">Konsultasi Chifheto</p><h2 id="consult-title">Diskusikan {{ $program['title'] }} di {{ $region['label'] }}.</h2><p>Sampaikan kebutuhan dan lokasi Anda. Ruang lingkup serta ketersediaan akan dikonfirmasi sebelum ada pengaturan lanjutan.</p></div>
        <x-wa-cta variant="primary" size="lg" :message="$waMessage" label="Kirim pertanyaan melalui WhatsApp" class="ch-button" />
    </section>
</article>
@endsection
