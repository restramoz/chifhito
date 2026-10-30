@extends('layouts.app')

@section('title', 'Jelajahi Kategori Layanan')
@section('meta_description', 'Cari dan jelajahi seluruh layanan industri di katalog ' . config('app.name', 'Najkaltare') . '.')

@section('content')
<section class="bg-[#F4F6F8] py-12 sm:py-16" aria-labelledby="catalog-heading">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}#layanan" class="inline-flex rounded-lg text-sm font-semibold text-[#006E5E] hover:text-[#111318] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00C2A8]">&larr; Kembali ke pilihan layanan</a>

        <div class="mt-6 max-w-3xl">
            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#006E5E]">Kategori Layanan</p>
            <h1 id="catalog-heading" class="mt-3 text-3xl font-black tracking-tight text-[#111318] sm:text-4xl">Jelajahi seluruh katalog</h1>
            <p class="mt-3 text-sm leading-relaxed text-[#535B66] sm:text-base">Cari berdasarkan nama layanan atau buka kategori untuk menelusuri seluruh daftar.</p>
        </div>

        <label for="catalog-search" class="mt-8 block text-sm font-bold text-[#111318]">Cari layanan</label>
        <input id="catalog-search" type="search" autocomplete="off" placeholder="Contoh: Teknisi Listrik" class="mt-2 w-full max-w-2xl rounded-xl border border-[#D7DDE3] bg-white px-4 py-3 text-sm shadow-sm focus:border-[#00A991] focus:outline-none focus:ring-2 focus:ring-[#00C2A8]/30" aria-describedby="catalog-count">
        <p id="catalog-count" class="mt-2 text-xs text-[#535B66]">{{ number_format($totalServices, 0, ',', '.') }} layanan tersedia</p>

        <div class="mt-6 space-y-3" id="catalog-categories">
            @foreach ($categories as $categoryName => $programs)
                <details class="catalog-category overflow-hidden rounded-2xl border border-[#E1E5EA] bg-white shadow-sm" data-catalog-category>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 font-bold text-[#111318] marker:hidden focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#00C2A8]">
                        <span>{{ $categoryName }}</span>
                        <span class="shrink-0 rounded-full bg-[#E8F7F4] px-3 py-1 text-xs font-bold text-[#006E5E]">{{ number_format($programs->count(), 0, ',', '.') }} layanan</span>
                    </summary>
                    <ul class="grid gap-2 border-t border-[#E8EBEF] p-4 sm:grid-cols-2 lg:grid-cols-3" role="list">
                        @foreach ($programs as $program)
                            <li data-catalog-item data-search-text="{{ mb_strtolower($program['title'] . ' ' . $categoryName) }}">
                                <a href="{{ route('program.show', $program['slug']) }}" class="flex min-h-11 items-center justify-between gap-3 rounded-lg border border-[#E8EBEF] bg-[#F8FAFB] px-3 py-2.5 text-sm font-medium text-[#252932] transition hover:border-[#00C2A8] hover:bg-white hover:text-[#006E5E] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00C2A8]">
                                    <span>{{ $program['title'] }}</span><span aria-hidden="true" class="shrink-0">&rarr;</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </details>
            @endforeach
        </div>
        <p id="catalog-empty" class="mt-8 hidden rounded-xl border border-[#E1E5EA] bg-white p-5 text-sm text-[#535B66]">Tidak ada layanan yang cocok dengan pencarian.</p>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('catalog-search');
    if (!input) return;
    const categories = Array.from(document.querySelectorAll('[data-catalog-category]'));
    const empty = document.getElementById('catalog-empty');
    const count = document.getElementById('catalog-count');
    const total = {{ $totalServices }};
    input.addEventListener('input', function () {
        const query = input.value.trim().toLocaleLowerCase();
        let visible = 0;
        categories.forEach(function (category) {
            let categoryVisible = 0;
            category.querySelectorAll('[data-catalog-item]').forEach(function (item) {
                const matches = !query || item.dataset.searchText.includes(query);
                item.hidden = !matches;
                if (matches) categoryVisible++;
            });
            category.hidden = categoryVisible === 0;
            if (query && categoryVisible) category.open = true;
            visible += categoryVisible;
        });
        empty.classList.toggle('hidden', visible > 0);
        count.textContent = query ? visible.toLocaleString('id-ID') + ' layanan cocok' : total.toLocaleString('id-ID') + ' layanan tersedia';
    });
});
</script>
@endsection
