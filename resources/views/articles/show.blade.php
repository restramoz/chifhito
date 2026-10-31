@extends('layouts.app')

@section('title', $article->seo_title ?: $article->title)
@section('meta_description', $article->meta_description ?: ($article->excerpt ?: Str::limit(strip_tags($article->content), 155)))

@push('schema')
{{-- Article Structured Data --}}
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "Article",
  "headline": {{ json_encode($article->title) }},
  "description": {{ json_encode($article->meta_description ?: $article->excerpt) }},
  "datePublished": "{{ ($article->published_at ?? $article->created_at)->toIso8601String() }}",
  "dateModified": "{{ $article->updated_at->toIso8601String() }}",
  @if($article->featured_image)
  "image": ["{{ url('/' . ltrim($article->featured_image, '/')) }}"],
  @endif
  "author": {
    "@type": "Organization",
    "name": "Tim {{ config('app.name', 'Chifheto') }}"
  },
  "publisher": {
    "@type": "Organization",
    "name": "{{ config('app.name', 'Chifheto') }}",
    "url": "{{ url('/') }}"
  }
}
</script>

@if(!empty($article->faqs) && is_array($article->faqs))
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    @foreach($article->faqs as $i => $faq)
    {
      "@type": "Question",
      "name": {{ json_encode($faq['q'] ?? '') }},
      "acceptedAnswer": {
        "@type": "Answer",
        "text": {{ json_encode($faq['a'] ?? '') }}
      }
    }{{ !$loop->last ? ',' : '' }}
    @endforeach
  ]
}
</script>
@endif
@endpush

@section('content')
{{-- Breadcrumbs --}}
<nav class="bg-neutral-100 border-b border-neutral-200 py-3 text-xs text-dark-mahogany" aria-label="Breadcrumb">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <ol class="flex items-center flex-wrap gap-x-2 gap-y-1" itemscope itemtype="https://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a href="{{ route('home') }}" class="hover:text-burnt-brick transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-safety-orange rounded" itemprop="item">
                    <span itemprop="name">Beranda</span>
                </a>
                <meta itemprop="position" content="1">
            </li>
            <li aria-hidden="true" class="text-neutral-400">/</li>
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a href="{{ route('articles.index') }}" class="hover:text-burnt-brick transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-safety-orange rounded" itemprop="item">
                    <span itemprop="name">Artikel</span>
                </a>
                <meta itemprop="position" content="2">
            </li>
            <li aria-hidden="true" class="text-neutral-400">/</li>
            <li class="text-industry-black font-semibold truncate max-w-xs" aria-current="page">{{ $article->title }}</li>
        </ol>
    </div>
</nav>

{{-- Article Header & Body --}}
<article class="py-12 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <header class="space-y-5 mb-8">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-neutral-100 text-burnt-brick border border-burnt-brick/20 uppercase tracking-wider">
                    Skema Kompetensi
                </span>
                @if($targetCity)
                    <a
                        href="{{ route('regional.city', $targetCity['slug']) }}"
                        class="px-3 py-1 rounded-full text-xs font-bold bg-neutral-100 text-dark-mahogany hover:text-burnt-brick border border-neutral-200 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-safety-orange"
                    >
                        Wilayah {{ $targetCity['name'] }}
                    </a>
                @endif
            </div>

            <h1 class="font-extrabold text-2xl sm:text-4xl text-industry-black leading-tight">
                {{ $article->title }}
            </h1>

            <div class="flex flex-wrap items-center gap-3 text-xs text-neutral-500 border-b border-neutral-200 pb-5">
                <time datetime="{{ ($article->published_at ?? $article->created_at)->toDateString() }}">
                    Diterbitkan: <strong class="text-dark-mahogany">{{ ($article->published_at ?? $article->created_at)->format('d F Y') }}</strong>
                </time>
                <span aria-hidden="true">&bull;</span>
                <span>Oleh: <strong class="text-dark-mahogany">Tim {{ config('app.name', 'Chifheto') }}</strong></span>
            </div>

            @if($article->excerpt)
                <p class="text-sm sm:text-base text-dark-mahogany leading-relaxed bg-neutral-50 p-5 rounded-r-xl border-l-4 border-safety-orange">
                    {{ $article->excerpt }}
                </p>
            @endif
        </header>

        {{-- Featured Image --}}
        @if($article->featured_image)
            <div class="mb-10 rounded-2xl overflow-hidden border border-neutral-200 shadow-sm bg-neutral-100">
                <img
                    src="/{{ ltrim($article->featured_image, '/') }}"
                    alt="{{ $article->title }}"
                    class="article-featured-image w-full"
                    loading="eager"
                >
            </div>
        @endif

        <style>
            .article-content {
                width: 100%;
                max-width: 100%;
                min-width: 0;
                overflow-wrap: anywhere;
            }
            .article-content .article-table-scroll {
                display: block;
                width: 100%;
                max-width: 100%;
                min-width: 0;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                margin: 1rem 0;
            }
            .article-content .article-table-scroll table {
                width: max-content;
                min-width: 600px;
                max-width: none;
                border-collapse: collapse;
                table-layout: auto;
            }
            .article-content .article-table-scroll th,
            .article-content .article-table-scroll td {
                border: 1px solid #E2EAE2;
                padding: 8px 10px;
                text-align: left;
                vertical-align: top;
                white-space: normal;
                overflow-wrap: anywhere;
            }
            .article-content .article-table-scroll th {
                background: #F0F5F0;
                font-weight: 700;
                color: #1F2937;
            }
        </style>

        {{-- Main Content --}}
        <div class="article-content prose prose-stone max-w-none text-sm sm:text-base leading-relaxed text-dark-mahogany
                    prose-headings:font-bold prose-headings:text-industry-black
                    prose-h2:text-xl prose-h2:mt-8 prose-h3:text-lg prose-h3:mt-6
                    prose-a:text-burnt-brick prose-a:no-underline hover:prose-a:underline hover:prose-a:text-safety-orange
                    prose-strong:text-industry-black
                    prose-blockquote:border-safety-orange prose-blockquote:bg-neutral-50 prose-blockquote:rounded-r-xl prose-blockquote:py-1 prose-blockquote:text-dark-mahogany
                    prose-ul:space-y-1 prose-ol:space-y-1
                    prose-table:w-full prose-table:border-collapse prose-table:text-sm
                    prose-th:border prose-th:border-neutral-200 prose-th:bg-neutral-100 prose-th:px-3 prose-th:py-2 prose-th:text-left
                    prose-td:border prose-td:border-neutral-200 prose-td:px-3 prose-td:py-2 prose-td:align-top">
            {!! $article->content !!}
        </div>

        <script>
            document.querySelectorAll('.article-content table').forEach(function (table) {
                if (table.parentElement.classList.contains('article-table-scroll')) return;

                const wrapper = document.createElement('div');
                wrapper.className = 'article-table-scroll';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            });
        </script>

        {{-- FAQ Section --}}
        @if(!empty($article->faqs) && is_array($article->faqs))
            <div class="mt-12 pt-8 border-t border-neutral-200 space-y-4">
                <h2 class="font-extrabold text-xl text-industry-black">
                    Pertanyaan Umum (FAQ)
                </h2>
                <div class="space-y-3" role="list">
                    @foreach($article->faqs as $i => $faq)
                        <details
                            class="group bg-white border border-neutral-200 rounded-xl overflow-hidden shadow-xs"
                            @if($loop->first) open @endif
                        >
                            <summary class="flex items-center justify-between px-5 py-4 font-bold text-sm text-industry-black cursor-pointer select-none list-none hover:bg-neutral-50 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-safety-orange">
                                <span>{{ $faq['q'] ?? '' }}</span>
                                <svg class="w-4 h-4 text-burnt-brick shrink-0 ml-3 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </summary>
                            <div class="px-5 pb-5 pt-2 text-sm text-dark-mahogany leading-relaxed">
                                {{ $faq['a'] ?? '' }}
                            </div>
                        </details>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Consultation CTA --}}
        <div class="mt-12 p-6 sm:p-8 rounded-2xl bg-industry-black text-white space-y-4 shadow-lg border border-dark-mahogany">
            <span class="inline-block px-3 py-1 rounded-full bg-dark-mahogany text-safety-orange text-[11px] font-bold uppercase tracking-wider border border-burnt-brick/40">
                Skema Kompetensi {{ config('app.name', 'Chifheto') }}
            </span>
            <h3 class="font-extrabold text-xl sm:text-2xl text-white">
                Konsultasikan Kebutuhan Program {{ config('app.name', 'Chifheto') }} Anda
            </h3>
            <p class="text-sm text-neutral-200 leading-relaxed max-w-2xl">
                Dapatkan informasi mengenai skema kompetensi, unit pelatihan, dan saran program yang sesuai melalui WhatsApp.
            </p>
            <div class="pt-2 flex flex-wrap gap-3">
                <x-wa-cta variant="primary" size="md" label="Konsultasi Program Sekarang" class="bg-safety-orange hover:bg-burnt-brick text-industry-black hover:text-white font-bold shadow-md" />
                <a href="{{ route('articles.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg border-2 border-neutral-400 text-white hover:bg-neutral-800 text-sm font-bold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-safety-orange">
                    &larr; Lihat Artikel Lainnya
                </a>
            </div>
        </div>

        {{-- Direct phone contacts — rendered at the Blade layer so they appear on every --}}
        {{-- published article page, independent of how the article content was generated. --}}
        {{-- Avoid duplicating when a generated article already contains the block. --}}
        @if(! str_contains((string) $article->content, 'article-consultation-numbers') && ! str_contains((string) $article->content, 'wa.me/628118500177'))
            <x-contact-numbers />
        @endif
    </div>
</article>

{{-- Related Articles --}}
@if($relatedArticles->isNotEmpty())
<section class="py-12 bg-neutral-50 border-t border-neutral-200" aria-label="Artikel terkait">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="font-extrabold text-xl text-industry-black mb-6">
            Artikel Lainnya
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($relatedArticles as $rel)
                <article class="bg-white rounded-2xl border border-neutral-200 p-5 space-y-2.5 flex flex-col justify-between shadow-sm hover:shadow-md hover:border-burnt-brick transition-all">
                    <div>
                        <time class="text-[10px] text-neutral-500 block" datetime="{{ ($rel->published_at ?? $rel->created_at)->toDateString() }}">
                            {{ ($rel->published_at ?? $rel->created_at)->format('d M Y') }}
                        </time>
                        <h3 class="font-bold text-sm text-industry-black hover:text-burnt-brick line-clamp-2 mt-1 transition-colors">
                            <a href="{{ route('articles.show', $rel->slug) }}" class="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-safety-orange rounded">
                                {{ $rel->title }}
                            </a>
                        </h3>
                        @if($rel->excerpt)
                            <p class="text-xs text-dark-mahogany line-clamp-2 mt-1">
                                {{ $rel->excerpt }}
                            </p>
                        @endif
                    </div>
                    <a href="{{ route('articles.show', $rel->slug) }}" class="text-xs font-bold text-burnt-brick hover:text-safety-orange pt-2 inline-flex items-center gap-1 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-safety-orange rounded">
                        <span>Baca Artikel &rarr;</span>
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
