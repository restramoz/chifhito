@extends('admin.layout')

@section('title', 'Edit Artikel: ' . $article->title . ' — Admin')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/jodit@4.1.16/es2021/jodit.min.css">
<style>
    .jodit-container {
        border-radius: 10px !important;
        border: 1px solid #7AB2B2 !important;
    }
    .jodit-toolbar-button__button {
        color: #09637E !important;
    }
</style>
@endpush

@section('content')
@php
    $provinces = collect($cities)->pluck('province')->filter()->unique()->sort()->values();
@endphp
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-navy-950 tracking-tight">
                Edit Artikel
            </h1>
            <p class="text-xs text-concrete-500">
                Perbarui konten, kelola optimasi SEO, atau ubah status publikasi artikel
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if($article->status === 'published')
                <a href="{{ route('articles.show', $article->slug) }}" target="_blank" rel="noopener noreferrer" class="px-3.5 py-2 text-xs font-bold text-navy-900 bg-navy-50 border border-navy-200 hover:bg-navy-100 rounded-lg transition inline-flex items-center gap-1">
                    <span>Lihat di Web</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
            @endif
            <a href="{{ route('admin.articles.index') }}" class="px-3.5 py-2 text-xs font-bold text-concrete-700 hover:text-navy-950 bg-white border border-concrete-300 rounded-lg transition">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- Structured Image Brief Display (if available) -->
    @if(!empty($article->image_brief))
    <div id="image-brief-container" class="bg-emerald-50/70 p-5 rounded-2xl border border-emerald-300 space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <h3 class="font-bold text-sm text-emerald-950">
                    Image Brief &amp; Prompt Google Flow
                </h3>
            </div>
            <span class="text-[10px] font-bold text-emerald-900 bg-emerald-200/70 px-2.5 py-0.5 rounded-full">
                Aspek Rasio: {{ $article->image_brief['aspect_ratio'] ?? '16:9' }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
            <div>
                <span class="font-bold text-emerald-950 block mb-0.5">Konsep Visual:</span>
                <p id="brief-concept" class="text-emerald-900 bg-white p-2.5 rounded-lg border border-emerald-200 leading-relaxed">{{ $article->image_brief['concept'] ?? '-' }}</p>
            </div>
            <div>
                <span class="font-bold text-emerald-950 block mb-0.5">Rekomendasi Alt Text:</span>
                <p id="brief-alt" class="text-emerald-900 bg-white p-2.5 rounded-lg border border-emerald-200 leading-relaxed">{{ $article->image_brief['recommended_alt'] ?? '-' }}</p>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-1">
                <span class="font-bold text-xs text-emerald-950">English Prompt Siap Salin ke Google Flow:</span>
                <button type="button" id="btn-copy-prompt" class="text-[11px] font-bold text-white bg-emerald-800 hover:bg-emerald-900 px-3 py-1 rounded-md transition inline-flex items-center gap-1 cursor-pointer">
                    <span id="copy-text">Salin Prompt Google Flow</span>
                </button>
            </div>
            <textarea id="brief-english-prompt" rows="3" readonly class="w-full text-xs font-mono p-3 bg-white border border-emerald-300 rounded-lg text-slate-800 focus:outline-none">{{ $article->image_brief['english_prompt'] ?? '' }}</textarea>
            <p class="text-[11px] text-emerald-800 mt-1">
                * Buat gambar di Google Flow, unduh hasilnya, lalu unggah file gambar tersebut melalui kolom "Ganti Gambar Unggulan" di bawah.
            </p>
        </div>
    </div>
    @endif

    <!-- Main Editor & Live Preview Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left: Form Input -->
        <div class="lg:col-span-7 space-y-5">
            <form id="article-form" method="POST" action="{{ route('admin.articles.update', $article->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Hidden inputs for image brief & faqs JSON -->
                <input type="hidden" name="image_brief" id="hidden_image_brief" value="{{ is_array(old('image_brief', $article->image_brief)) ? json_encode(old('image_brief', $article->image_brief)) : old('image_brief', $article->image_brief) }}">
                <input type="hidden" name="faqs" id="hidden_faqs" value="{{ is_array(old('faqs', $article->faqs)) ? json_encode(old('faqs', $article->faqs)) : old('faqs', $article->faqs) }}">

                <div class="bg-white p-6 rounded-2xl border border-concrete-300 shadow-sm space-y-4">
                    <!-- Judul -->
                    <div>
                        <label for="title" class="block text-xs font-bold text-navy-950 mb-1 uppercase tracking-wider">
                            Judul Artikel *
                        </label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            required
                            value="{{ old('title', $article->title) }}"
                            class="w-full px-3.5 py-2.5 text-sm font-bold rounded-lg border border-concrete-300 focus:outline-none focus:ring-2 focus:ring-safety-500"
                        >
                    </div>

                    <!-- Slug URL -->
                    <div>
                        <label for="slug" class="block text-xs font-bold text-navy-950 mb-1 uppercase tracking-wider">
                            Slug URL
                        </label>
                        <input
                            type="text"
                            id="slug"
                            name="slug"
                            value="{{ old('slug', $article->slug) }}"
                            class="w-full px-3.5 py-2 text-xs font-mono rounded-lg border border-concrete-300 bg-concrete-50 focus:outline-none focus:ring-2 focus:ring-safety-500"
                        >
                    </div>

                    <!-- Target Wilayah Selector with Province Filter & Search -->
                    <div class="p-3.5 rounded-xl bg-concrete-50 border border-concrete-200 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <label for="target_city_slug" class="block text-xs font-bold text-navy-950 uppercase tracking-wider">
                                Tautkan ke Wilayah (Kota / Kabupaten)
                            </label>
                            <span class="text-[10px] text-concrete-500">Kosongkan jika skala nasional</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label for="filter_target_province" class="block text-[11px] text-concrete-600 mb-1">Filter Provinsi</label>
                                <select id="filter_target_province" class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-concrete-300 bg-white focus:outline-none focus:ring-2 focus:ring-safety-500">
                                    <option value="">Semua Provinsi ({{ $provinces->count() }})</option>
                                    @foreach($provinces as $prov)
                                        <option value="{{ $prov }}">{{ $prov }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="search_target_city" class="block text-[11px] text-concrete-600 mb-1">Cari Nama Kota / Kabupaten</label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        id="search_target_city"
                                        placeholder="Ketik nama kota, misal: Surabaya..."
                                        class="w-full pl-2.5 pr-6 py-1.5 text-xs rounded-lg border border-concrete-300 bg-white focus:outline-none focus:ring-2 focus:ring-safety-500"
                                    >
                                    <button type="button" id="btn_clear_target_search" class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-concrete-400 hover:text-concrete-700 text-xs">&times;</button>
                                </div>
                            </div>
                        </div>
                        <div>
                            <select id="target_city_slug" name="target_city_slug" class="w-full px-3 py-2 text-xs rounded-lg border border-concrete-300 bg-white focus:outline-none focus:ring-2 focus:ring-safety-500">
                                <option value="">-- Nasional / Tanpa Wilayah Tertentu --</option>
                                @foreach($cities as $city)
                                    <option
                                        value="{{ $city['slug'] }}"
                                        data-province="{{ $city['province'] }}"
                                        data-name="{{ $city['name'] }}"
                                        {{ old('target_city_slug', $article->target_city_slug) === $city['slug'] ? 'selected' : '' }}
                                    >
                                        {{ $city['name'] }} ({{ $city['province'] }})
                                    </option>
                                @endforeach
                            </select>
                            <p id="target_city_feedback" class="text-[10px] text-concrete-500 mt-1"></p>
                        </div>
                    </div>

                    <!-- Ringkasan / Excerpt -->
                    <div>
                        <label for="excerpt" class="block text-xs font-bold text-navy-950 mb-1 uppercase tracking-wider">
                            Ringkasan / Excerpt
                        </label>
                        <textarea
                            id="excerpt"
                            name="excerpt"
                            rows="2"
                            placeholder="Ringkasan eksekutif 2-3 kalimat mengenai pokok bahasan artikel..."
                            class="w-full px-3.5 py-2 text-xs rounded-lg border border-concrete-300 focus:outline-none focus:ring-2 focus:ring-safety-500"
                        >{{ old('excerpt', $article->excerpt) }}</textarea>
                    </div>

                    <!-- Featured Image Upload -->
                    <div>
                        <label for="featured_image" class="block text-xs font-bold text-navy-950 mb-1 uppercase tracking-wider">
                            Gambar Unggulan (Featured Image)
                        </label>

                        @if(!empty($article->featured_image))
                            <div class="mb-3 p-3 bg-concrete-50 rounded-xl border border-concrete-200 flex items-center gap-3">
                                <img src="{{ asset($article->featured_image) }}" alt="{{ $article->title }}" class="w-20 h-14 object-cover rounded-lg border border-concrete-300 shrink-0">
                                <div class="text-xs">
                                    <span class="font-bold text-navy-950 block">Gambar saat ini</span>
                                    <span class="text-concrete-500 text-[11px] break-all">{{ $article->featured_image }}</span>
                                    <p class="text-[10px] text-concrete-400 mt-0.5">Unggah file baru di bawah jika ingin mengganti gambar.</p>
                                </div>
                            </div>
                        @endif

                        <input
                            type="file"
                            id="featured_image"
                            name="featured_image"
                            accept="image/jpeg,image/png,image/webp"
                            class="w-full text-xs text-concrete-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-navy-900 file:text-safety-400 hover:file:bg-navy-950 file:transition cursor-pointer"
                        >
                        <p class="text-[11px] text-concrete-500 mt-1">
                            Format diizinkan: JPG, PNG, WebP (Maks. 5MB). SVG ditolak demi keamanan.
                        </p>
                    </div>

                    <!-- Jodit Editor -->
                    <div>
                        <label for="content_editor" class="block text-xs font-bold text-navy-950 mb-1 uppercase tracking-wider">
                            Konten Artikel (HTML) *
                        </label>
                        <textarea id="content_editor" name="content">{{ old('content', $article->content) }}</textarea>
                    </div>

                    <!-- SEO Fields -->
                    <div class="border-t border-concrete-200 pt-4 space-y-3">
                        <span class="text-xs font-bold text-navy-950 block uppercase tracking-wider">
                            Optimasi Mesin Pencari (SEO)
                        </span>
                        <div>
                            <label for="seo_title" class="block text-[11px] text-concrete-600 mb-0.5">SEO Title (Max 60 Karakter)</label>
                            <input
                                type="text"
                                id="seo_title"
                                name="seo_title"
                                value="{{ old('seo_title', $article->seo_title) }}"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-concrete-300 focus:outline-none focus:ring-2 focus:ring-safety-500"
                            >
                        </div>
                        <div>
                            <label for="meta_description" class="block text-[11px] text-concrete-600 mb-0.5">Meta Description (Max 155 Karakter)</label>
                            <textarea
                                id="meta_description"
                                name="meta_description"
                                rows="2"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-concrete-300 focus:outline-none focus:ring-2 focus:ring-safety-500"
                            >{{ old('meta_description', $article->meta_description) }}</textarea>
                        </div>
                    </div>

                    <!-- Status & Submit -->
                    <div class="border-t border-concrete-200 pt-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <label for="status" class="block text-xs font-bold text-navy-950 mb-1 uppercase tracking-wider">
                                Status Publikasi
                            </label>
                            <select id="status" name="status" class="px-3 py-1.5 text-xs rounded-lg border border-concrete-300 bg-concrete-50">
                                <option value="draft" {{ old('status', $article->status) === 'draft' ? 'selected' : '' }}>Draft (Belum Publik)</option>
                                <option value="published" {{ old('status', $article->status) === 'published' ? 'selected' : '' }}>Published (Tayang)</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                type="submit"
                                name="action"
                                value="draft"
                                onclick="document.getElementById('status').value='draft'"
                                class="px-4 py-2 text-xs font-bold text-concrete-700 bg-white border border-concrete-300 hover:bg-concrete-100 rounded-lg transition cursor-pointer"
                            >
                                Simpan Draf
                            </button>
                            <button
                                type="submit"
                                name="action"
                                value="published"
                                onclick="document.getElementById('status').value='published'"
                                class="px-5 py-2 rounded-lg bg-safety-500 hover:bg-safety-600 text-navy-950 font-extrabold text-xs shadow-sm transition cursor-pointer"
                            >
                                Perbarui &amp; Publikasikan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Right: Real-time Live Preview (Tanpa Simpan) -->
        <div class="lg:col-span-5 sticky top-24 space-y-4">
            <div class="bg-white p-5 rounded-2xl border border-concrete-300 shadow-sm space-y-3">
                <div class="flex items-center justify-between border-b border-concrete-200 pb-2">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-safety-600">
                        PREVIEW LANGSUNG (REAL-TIME)
                    </span>
                    <span class="text-[10px] text-concrete-600 bg-concrete-100 px-2 py-0.5 rounded-full">
                        Live Preview
                    </span>
                </div>

                <!-- Featured Image Preview -->
                <div id="preview-image-box" class="{{ !empty($article->featured_image) ? '' : 'hidden' }} rounded-xl overflow-hidden border border-concrete-200 max-h-48 bg-slate-100">
                    <img id="preview-image-tag" src="{{ !empty($article->featured_image) ? asset($article->featured_image) : '' }}" alt="Featured preview" class="w-full h-44 object-cover">
                </div>

                <!-- Title & Excerpt Preview -->
                <div class="space-y-2">
                    <h2 id="preview-title" class="font-bold text-lg text-navy-950 leading-snug">
                        {{ $article->title ?: '(Judul artikel akan muncul di sini)' }}
                    </h2>
                    <p id="preview-excerpt" class="text-xs text-concrete-600 leading-relaxed italic bg-concrete-50 p-3 rounded-lg border-l-4 border-safety-500">
                        {{ $article->excerpt ?: '(Ringkasan excerpt akan tampil di sini)' }}
                    </p>
                </div>

                <div class="h-px bg-concrete-200"></div>

                <!-- Content Body Preview -->
                <div class="space-y-2">
                    <span class="text-[10px] font-bold text-concrete-500 uppercase tracking-wider block">
                        Isi Konten Artikel
                    </span>
                    <div id="preview-content" class="prose prose-slate max-w-none text-xs text-concrete-800 leading-relaxed max-h-[500px] overflow-y-auto p-4 rounded-xl bg-white border border-concrete-200">
                        {!! $article->content !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/jodit@4.1.16/es2021/jodit.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Inisialisasi Jodit Editor
    const editor = Jodit.make('#content_editor', {
        placeholder: 'Tulis konten artikel artikel teknis di sini...',
        height: 480,
        toolbarAdaptive: false,
        uploader: {
            url: '{{ route('admin.upload.image') }}',
            format: 'json',
            fieldName: 'image',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            isSuccess: function (resp) {
                return resp && resp.success === true;
            },
            process: function (resp) {
                return {
                    files: resp.files || [resp.url],
                    path: resp.url,
                    baseurl: '',
                    error: resp.message || '',
                    msg: resp.message || ''
                };
            },
            error: function (e) {
                alert('Gagal mengunggah gambar: ' + (e.message || JSON.stringify(e)));
            }
        }
    });

    // 2. Real-time Live Preview synchronization
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    const excerptInput = document.getElementById('excerpt');
    const featuredImageInput = document.getElementById('featured_image');

    const previewTitle = document.getElementById('preview-title');
    const previewExcerpt = document.getElementById('preview-excerpt');
    const previewContent = document.getElementById('preview-content');
    const previewImageBox = document.getElementById('preview-image-box');
    const previewImageTag = document.getElementById('preview-image-tag');

    function syncLivePreview() {
        // Title
        previewTitle.textContent = titleInput.value.trim() || '(Judul artikel akan muncul di sini)';

        // Excerpt
        previewExcerpt.textContent = excerptInput.value.trim() || '(Ringkasan excerpt akan tampil di sini)';

        // Content from Jodit
        const html = editor.value;
        if (html && html.trim() !== '') {
            previewContent.innerHTML = html;
        } else {
            previewContent.innerHTML = '<p class="text-concrete-400 italic">Mulai mengetik untuk melihat pratinjau konten.</p>';
        }
    }

    titleInput.addEventListener('input', syncLivePreview);
    excerptInput.addEventListener('input', syncLivePreview);
    editor.events.on('change', syncLivePreview);

    // Featured image preview via FileReader
    if (featuredImageInput) {
        featuredImageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImageTag.src = e.target.result;
                    previewImageBox.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Helper: setup combined province filter and search for city select
    function setupCitySelector(config) {
        const provinceSelect = document.getElementById(config.provinceSelectId);
        const searchInput = document.getElementById(config.searchInputId);
        const citySelect = document.getElementById(config.citySelectId);
        const clearBtn = config.clearBtnId ? document.getElementById(config.clearBtnId) : null;
        const feedbackEl = config.feedbackElId ? document.getElementById(config.feedbackElId) : null;

        if (!citySelect) return { setSelected: function () {} };

        const defaultOption = citySelect.options[0] ? {
            value: citySelect.options[0].value,
            text: citySelect.options[0].textContent
        } : null;

        const allOptions = [];
        for (let i = (defaultOption ? 1 : 0); i < citySelect.options.length; i++) {
            const opt = citySelect.options[i];
            allOptions.push({
                value: opt.value,
                text: opt.textContent,
                province: (opt.getAttribute('data-province') || '').toLowerCase().trim(),
                name: (opt.getAttribute('data-name') || opt.textContent).toLowerCase().trim(),
                rawProvince: opt.getAttribute('data-province') || '',
                rawName: opt.getAttribute('data-name') || opt.textContent
            });
        }

        let selectedValue = citySelect.value;

        function applyFilter() {
            const selectedProv = provinceSelect ? provinceSelect.value.toLowerCase().trim() : '';
            const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';

            if (clearBtn) {
                clearBtn.classList.toggle('hidden', !keyword);
            }

            const filtered = allOptions.filter(function (item) {
                const matchProv = !selectedProv || item.province === selectedProv;
                const matchSearch = !keyword || item.name.includes(keyword) || item.text.toLowerCase().includes(keyword);
                return matchProv && matchSearch;
            });

            citySelect.innerHTML = '';
            if (defaultOption) {
                const defOpt = document.createElement('option');
                defOpt.value = defaultOption.value;
                defOpt.textContent = defaultOption.text;
                citySelect.appendChild(defOpt);
            }

            let hasSelectedInFiltered = false;
            filtered.forEach(function (item) {
                const opt = document.createElement('option');
                opt.value = item.value;
                opt.textContent = item.text;
                opt.setAttribute('data-province', item.rawProvince);
                opt.setAttribute('data-name', item.rawName);
                if (item.value === selectedValue) {
                    opt.selected = true;
                    hasSelectedInFiltered = true;
                }
                citySelect.appendChild(opt);
            });

            if (selectedValue && !hasSelectedInFiltered) {
                const currentItem = allOptions.find(function (item) { return item.value === selectedValue; });
                if (currentItem) {
                    const preservedOpt = document.createElement('option');
                    preservedOpt.value = currentItem.value;
                    preservedOpt.textContent = currentItem.text + ' (Sedang Dipilih)';
                    preservedOpt.setAttribute('data-province', currentItem.rawProvince);
                    preservedOpt.setAttribute('data-name', currentItem.rawName);
                    preservedOpt.selected = true;
                    citySelect.appendChild(preservedOpt);
                }
            }

            if (feedbackEl) {
                if (keyword || selectedProv) {
                    feedbackEl.textContent = 'Menampilkan ' + filtered.length + ' dari ' + allOptions.length + ' kota/kabupaten';
                } else {
                    feedbackEl.textContent = 'Total ' + allOptions.length + ' kota/kabupaten terdaftar';
                }
            }
        }

        citySelect.addEventListener('change', function () {
            selectedValue = this.value;
        });

        if (provinceSelect) {
            provinceSelect.addEventListener('change', applyFilter);
        }

        if (searchInput) {
            searchInput.addEventListener('input', applyFilter);
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                searchInput.value = '';
                applyFilter();
                searchInput.focus();
            });
        }

        applyFilter();

        return {
            setSelected: function (val) {
                selectedValue = val;
                applyFilter();
                citySelect.value = val;
            }
        };
    }

    const targetCitySelector = setupCitySelector({
        provinceSelectId: 'filter_target_province',
        searchInputId: 'search_target_city',
        citySelectId: 'target_city_slug',
        clearBtnId: 'btn_clear_target_search',
        feedbackElId: 'target_city_feedback'
    });

    const form = document.getElementById('article-form');
    if (form) {
        form.addEventListener('submit', function () {
            if (editor) {
                document.getElementById('content_editor').value = editor.value;
            }
        });
    }

    const btnCopyPrompt = document.getElementById('btn-copy-prompt');
    const copyText = document.getElementById('copy-text');
    const briefEnglishPrompt = document.getElementById('brief-english-prompt');

    if (btnCopyPrompt && briefEnglishPrompt) {
        btnCopyPrompt.addEventListener('click', function () {
            if (!briefEnglishPrompt.value) return;

            navigator.clipboard.writeText(briefEnglishPrompt.value).then(function () {
                copyText.textContent = 'Berhasil Disalin!';
                setTimeout(function () {
                    copyText.textContent = 'Salin Prompt Google Flow';
                }, 2000);
            }).catch(function () {
                briefEnglishPrompt.select();
                document.execCommand('copy');
                copyText.textContent = 'Berhasil Disalin!';
                setTimeout(function () {
                    copyText.textContent = 'Salin Prompt Google Flow';
                }, 2000);
            });
        });
    }

    syncLivePreview();
});
</script>
@endpush
