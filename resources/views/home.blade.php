@extends('layouts.app')

@section('title', 'Pelatihan Teknis dan Industri')
@section('meta_description', 'Jelajahi katalog pelatihan teknis Chifheto berdasarkan topik. Temukan program yang sesuai dan konsultasikan kebutuhan tim Anda.')

@section('content')
@php
    $training = collect(app(\App\Services\SchemeService::class)->trainings());
    $categories = $training->groupBy('category_label')->sortKeys();
    $provinces = resolve(\App\Services\RegionalService::class)->getAllProvinces();
    $provinceGroups = collect($provinces)->groupBy('island')->sortKeys();
    $latestArticles = $latestArticles ?? collect();
@endphp
<main>
    <section class="ch-hero">
        <div class="ch-hero-copy">
            <p class="ch-eyebrow"><span></span> Chifheto · Pelatihan profesional</p>
            <h1>Kompetensi teknis,<br><em>dibangun untuk kerja nyata.</em></h1>
            <p class="ch-lede">Temukan pelatihan dari bidang pertambangan, migas, engineering, keselamatan, dan operasi industri. Mulai dari topik yang sedang Anda cari.</p>
            <form class="ch-search" action="{{ route('catalog.index') }}" method="GET">
                <label class="sr-only" for="training-search">Cari topik pelatihan</label>
                <span aria-hidden="true">⌕</span>
                <input id="training-search" name="q" type="search" placeholder="Cari topik atau nama pelatihan" autocomplete="off">
                <button type="submit">Cari pelatihan <span aria-hidden="true">→</span></button>
            </form>
            <div class="ch-proofline"><strong>{{ number_format($training->count(), 0, ',', '.') }}</strong> judul pelatihan dalam katalog <i></i> Konsultasi sesuai kebutuhan tim</div>
        </div>
        <div class="ch-hero-photo">
            <img src="/images/categories/pertambangan-migas-geologi.jpg" alt="Profesional melakukan inspeksi teknis di lingkungan industri" width="900" height="1100" fetchpriority="high">
            <div class="ch-photo-note"><span>01 / KATALOG</span><b>Pengetahuan teknis<br>untuk lingkungan industri.</b></div>
        </div>
        <div class="ch-hero-index" aria-hidden="true">TRAINING · ENGINEERING · OPERATIONS</div>
    </section>

    <section class="ch-section ch-categories" id="kategori">
        <div class="ch-section-head"><div><p class="ch-eyebrow">Jelajahi berdasarkan bidang</p><h2>Mulai dari topiknya.</h2></div><a class="ch-text-link" href="{{ route('catalog.index') }}">Lihat seluruh katalog <span>↗</span></a></div>
        <div class="ch-category-list">
            @foreach ($categories as $category => $items)
                <a class="ch-category-row" href="{{ route('catalog.index', ['category' => $category]) }}">
                    <span class="ch-category-no">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="ch-category-name">{{ $category }}</span>
                    <span class="ch-category-count">{{ $items->count() }} topik</span>
                    <span class="ch-arrow" aria-hidden="true">↗</span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="ch-section ch-regions" id="wilayah" aria-labelledby="wilayah-heading">
        <div class="ch-region-intro">
            <p class="ch-eyebrow">Jelajahi berdasarkan lokasi</p>
            <h2 id="wilayah-heading">Temukan wilayah<br><em>di seluruh Indonesia.</em></h2>
            <p>Pilih provinsi untuk melihat kota dan kabupaten, lalu buka daftar kecamatan dari halaman wilayah tersebut.</p>
            <strong>{{ count($provinces) }} provinsi · Data wilayah administratif BPS</strong>
            <label class="sr-only" for="province-search">Cari provinsi</label>
            <input class="ch-region-search" id="province-search" type="search" placeholder="Cari nama provinsi" autocomplete="off" aria-describedby="province-search-count">
            <span class="ch-region-count" id="province-search-count" aria-live="polite">{{ count($provinces) }} provinsi</span>
        </div>
        <div class="ch-region-directory">
            @foreach ($provinceGroups as $island => $islandProvinces)
                <section class="ch-region-group" aria-labelledby="region-group-{{ \Illuminate\Support\Str::slug($island) }}">
                    <h3 id="region-group-{{ \Illuminate\Support\Str::slug($island) }}">{{ $island }} <span>{{ $islandProvinces->count() }}</span></h3>
                    <ul>
                        @foreach ($islandProvinces as $province)
                            <li data-province-item data-province-name="{{ mb_strtolower($province['name']) }}">
                                <a href="{{ route('regional.province', $province['slug']) }}">{{ $province['name'] }} <span aria-hidden="true">↗</span></a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    </section>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('province-search');
            if (!input) return;
            const items = Array.from(document.querySelectorAll('[data-province-item]'));
            const count = document.getElementById('province-search-count');
            input.addEventListener('input', function () {
                const query = input.value.trim().toLocaleLowerCase();
                let visible = 0;
                items.forEach(function (item) {
                    const match = !query || item.dataset.provinceName.includes(query);
                    item.hidden = !match;
                    if (match) visible++;
                });
                document.querySelectorAll('.ch-region-group').forEach(function (group) {
                    group.hidden = !group.querySelector('[data-province-item]:not([hidden])');
                });
                count.textContent = visible.toLocaleString('id-ID') + ' provinsi' + (query ? ' cocok' : '');
            });
        });
    </script>

    <section class="ch-feature-band">
        <div><p class="ch-eyebrow">Pilih dengan informasi yang jelas</p><h2>Ruang untuk membahas<br>kebutuhan Anda.</h2></div>
        <div class="ch-feature-copy"><p>Setiap judul dalam katalog berasal dari daftar pelatihan sumber. Lingkup, jadwal, biaya, dan ketentuan program dikonfirmasi saat konsultasi agar informasi yang Anda terima sesuai kebutuhan.</p><x-wa-cta variant="primary" size="lg" label="Diskusikan kebutuhan pelatihan" class="ch-button" /></div>
        <span class="ch-band-mark" aria-hidden="true">C.</span>
    </section>

    @if ($latestArticles->isNotEmpty())
    <section class="ch-section ch-journal">
        <div class="ch-section-head"><div><p class="ch-eyebrow">Wawasan teknis</p><h2>Dari jurnal Chifheto.</h2></div><a class="ch-text-link" href="{{ route('articles.index') }}">Semua artikel <span>↗</span></a></div>
        <div class="ch-article-list">@foreach ($latestArticles as $article)<a href="{{ route('articles.show', $article->slug) }}"><span>{{ optional($article->published_at)->format('d.m.Y') }}</span><b>{{ $article->title }}</b><i aria-hidden="true">↗</i></a>@endforeach</div>
    </section>
    @endif
</main>
@endsection
