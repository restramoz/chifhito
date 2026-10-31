@extends('layouts.app')

@section('title', 'Katalog Pelatihan Profesional')
@section('meta_description', 'Cari pelatihan teknis Chifheto berdasarkan kategori dan topik.')

@section('content')
<section class="ch-catalog-page">
    <div class="ch-catalog-head">
        <p class="ch-eyebrow">CHIFHETO / KATALOG PELATIHAN</p>
        <h1>Temukan pelatihan<br><em>berdasarkan bidang.</em></h1>
        <p>Jelajahi {{ number_format($availableTrainingCount, 0, ',', '.') }} judul pelatihan dari katalog Ayana.</p>

        <form class="ch-search ch-catalog-search" action="{{ route('catalog.index') }}" method="GET" role="search">
            <label class="sr-only" for="catalog-search">Cari judul atau bidang pelatihan</label>
            <span aria-hidden="true">⌕</span>
            <input id="catalog-search" name="q" type="search" value="{{ $searchQuery }}" placeholder="Cari nama atau bidang pelatihan">
            @if($selectedCategory !== '')
                <input type="hidden" name="category" value="{{ $selectedCategory }}">
            @endif
            <button type="submit">Cari <span aria-hidden="true">→</span></button>
        </form>
    </div>

    <nav class="ch-filter-tabs" aria-label="Kategori pelatihan">
        <a class="{{ $selectedCategory === '' ? 'active' : '' }}" href="{{ route('catalog.index', $searchQuery !== '' ? ['q' => $searchQuery] : []) }}" @if($selectedCategory === '') aria-current="page" @endif>
            <span>Semua bidang</span><small>{{ number_format($availableTrainingCount, 0, ',', '.') }}</small>
        </a>
        @foreach ($trainingCategories as $category)
            <a class="{{ $selectedCategory === $category ? 'active' : '' }}" href="{{ route('catalog.index', array_filter(['category' => $category, 'q' => $searchQuery], static fn ($value) => $value !== '')) }}" @if($selectedCategory === $category) aria-current="page" @endif>
                <span>{{ $category }}</span><small>{{ number_format($categoryCounts[$category] ?? 0, 0, ',', '.') }}</small>
            </a>
        @endforeach
        @if($hasActiveFilters)
            <a class="ch-filter-reset" href="{{ route('catalog.index') }}">Hapus filter <span aria-hidden="true">×</span></a>
        @endif
    </nav>

    <div class="ch-catalog-summary" aria-live="polite">
        @if($hasActiveFilters)
            <p><strong>{{ number_format($totalServices, 0, ',', '.') }}</strong> judul ditemukan dari {{ number_format($availableTrainingCount, 0, ',', '.') }} pelatihan.</p>
        @else
            <p>Menampilkan seluruh <strong>{{ number_format($totalServices, 0, ',', '.') }}</strong> judul dari katalog Ayana.</p>
        @endif
        @if($selectedCategory !== '')<span>{{ $selectedCategory }}</span>@endif
        @if($searchQuery !== '')<span>Pencarian: “{{ $searchQuery }}”</span>@endif
    </div>

    @if($categories->isNotEmpty())
        <div class="ch-catalog-results" id="catalog-categories">
            @foreach ($categories as $categoryName => $programs)
                <section class="ch-catalog-group" aria-labelledby="catalog-category-{{ $loop->index }}">
                    <header class="ch-catalog-group-heading">
                        <span class="ch-category-no">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <h2 id="catalog-category-{{ $loop->index }}">{{ $categoryName }}</h2>
                        <span class="ch-category-count">{{ number_format($programs->count(), 0, ',', '.') }} judul</span>
                    </header>
                    <ul>
                        @foreach ($programs as $program)
                            <li data-catalog-item>
                                <a href="{{ route('program.show', $program['slug']) }}">
                                    <span><small>PELATIHAN · {{ $program['source_file'] }}</small>{{ $program['title'] }}</span>
                                    <i aria-hidden="true">↗</i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    @elseif($hasActiveFilters)
        <div class="ch-empty ch-catalog-empty" role="status">
            <h2>Tidak ada pelatihan yang cocok.</h2>
            <p>Coba kata kunci lain atau hapus filter untuk melihat kembali seluruh katalog.</p>
            <a href="{{ route('catalog.index') }}">Tampilkan semua pelatihan <span aria-hidden="true">→</span></a>
        </div>
    @else
        <div class="ch-empty ch-catalog-empty" role="status">
            <h2>Katalog pelatihan belum tersedia.</h2>
            <p>Daftar sumber belum menghasilkan judul yang dapat ditampilkan.</p>
        </div>
    @endif
</section>
@endsection
