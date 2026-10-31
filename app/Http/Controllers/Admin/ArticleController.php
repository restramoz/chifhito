<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\HtmlSanitizerService;
use App\Services\ImageUploadService;
use App\Services\OllamaService;
use App\Services\RegionalService;
use App\Services\SchemeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function __construct(private readonly SchemeService $schemes) {}
    /**
     * Display a listing of articles with optional filters.
     */
    public function index(Request $request): View
    {
        $query = Article::latest();

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('q')) {
            $keyword = trim((string) $request->query('q'));
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%")
                    ->orWhere('target_city_slug', 'like', "%{$keyword}%");
            });
        }

        $articles = $query->paginate(15)->withQueryString();

        return view('admin.articles.index', compact('articles'));
    }

    /**
     * Show the form for creating a new article (with AI generator).
     */
    public function create(RegionalService $regionalService, SchemeService $schemeService): View
    {
        $cities = $regionalService->getAllCities();
        $programs = $schemeService->trainings();

        return view('admin.articles.create', compact('cities', 'programs'));
    }

    /**
     * Store a newly created article in storage.
     */
    public function store(
        Request $request,
        HtmlSanitizerService $sanitizer,
        ImageUploadService $imageUploader
    ): RedirectResponse {
        // Pre-process action button if present (e.g. action="draft" or action="publish")
        if ($request->filled('action')) {
            $action = strtolower(trim((string) $request->input('action')));
            if (in_array($action, ['publish', 'published'])) {
                $request->merge(['status' => 'published']);
            } elseif (in_array($action, ['draft', 'save_draft'])) {
                $request->merge(['status' => 'draft']);
            }
        }

        // Decode JSON strings for image_brief and faqs if submitted from form hidden inputs
        if ($request->has('image_brief')) {
            $brief = $request->input('image_brief');
            if (is_string($brief)) {
                $brief = trim($brief);
                $decoded = $brief !== '' ? json_decode($brief, true) : null;
                $request->merge(['image_brief' => (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : null]);
            }
        }

        if ($request->has('faqs')) {
            $faqs = $request->input('faqs');
            if (is_string($faqs)) {
                $faqs = trim($faqs);
                $decoded = $faqs !== '' ? json_decode($faqs, true) : null;
                $request->merge(['faqs' => (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : null]);
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:articles,slug',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'status' => 'required|in:draft,published',
            'target_city_slug' => 'nullable|string|max:255',
            'target_district_slug' => 'nullable|string|max:255',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'featured_image' => 'nullable|file|image|mimes:jpeg,jpg,png,webp|max:5120',
            'image_brief' => 'nullable|array',
            'faqs' => 'nullable|array',
        ]);

        // Auto-generate slug if missing
        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['title']);

        // Prevent slug collisions
        $originalSlug = $slug;
        $counter = 1;
        while (Article::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }
        $validated['slug'] = $slug;

        // Sanitize HTML content before persisting
        $validated['content'] = $sanitizer->sanitize($validated['content']);

        // Handle featured image upload directly to public/uploads/articles
        if ($request->hasFile('featured_image')) {
            $uploadResult = $imageUploader->storeImage($request->file('featured_image'));
            if (! $uploadResult['success']) {
                return back()->withInput()->withErrors(['featured_image' => $uploadResult['error']]);
            }
            $validated['featured_image'] = $uploadResult['path'];
        }

        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        } else {
            $validated['published_at'] = null;
        }

        try {
            DB::beginTransaction();
            Article::create($validated);
            DB::commit();

            return redirect()->route('admin.articles.index')
                ->with('success', 'Artikel berhasil disimpan dengan status '.ucfirst($validated['status']).'.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->withInput()->with('error', 'Gagal menyimpan artikel: '.$e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified article.
     */
    public function edit(int $id, RegionalService $regionalService): View
    {
        $article = Article::findOrFail($id);
        $cities = $regionalService->getAllCities();

        return view('admin.articles.edit', compact('article', 'cities'));
    }

    /**
     * Update the specified article in storage.
     */
    public function update(
        Request $request,
        int $id,
        HtmlSanitizerService $sanitizer,
        ImageUploadService $imageUploader
    ): RedirectResponse {
        $article = Article::findOrFail($id);

        // Pre-process action button if present
        if ($request->filled('action')) {
            $action = strtolower(trim((string) $request->input('action')));
            if (in_array($action, ['publish', 'published'])) {
                $request->merge(['status' => 'published']);
            } elseif (in_array($action, ['draft', 'save_draft'])) {
                $request->merge(['status' => 'draft']);
            }
        }

        // Decode JSON strings for image_brief and faqs if submitted from form hidden inputs
        if ($request->has('image_brief')) {
            $brief = $request->input('image_brief');
            if (is_string($brief)) {
                $brief = trim($brief);
                $decoded = $brief !== '' ? json_decode($brief, true) : null;
                $request->merge(['image_brief' => (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : null]);
            }
        }

        if ($request->has('faqs')) {
            $faqs = $request->input('faqs');
            if (is_string($faqs)) {
                $faqs = trim($faqs);
                $decoded = $faqs !== '' ? json_decode($faqs, true) : null;
                $request->merge(['faqs' => (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : null]);
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:articles,slug,'.$article->id,
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'status' => 'required|in:draft,published',
            'target_city_slug' => 'nullable|string|max:255',
            'target_district_slug' => 'nullable|string|max:255',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'featured_image' => 'nullable|file|image|mimes:jpeg,jpg,png,webp|max:5120',
            'image_brief' => 'nullable|array',
            'faqs' => 'nullable|array',
        ]);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['title']);

        $originalSlug = $slug;
        $counter = 1;
        while (Article::where('slug', $slug)->where('id', '!=', $article->id)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }
        $validated['slug'] = $slug;

        // Sanitize content
        $validated['content'] = $sanitizer->sanitize($validated['content']);

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $uploadResult = $imageUploader->storeImage($request->file('featured_image'));
            if (! $uploadResult['success']) {
                return back()->withInput()->withErrors(['featured_image' => $uploadResult['error']]);
            }
            $validated['featured_image'] = $uploadResult['path'];
        } else {
            unset($validated['featured_image']);
        }

        // Preserve existing image_brief and faqs if omitted or null
        if (! array_key_exists('image_brief', $validated) || ($validated['image_brief'] === null && ! empty($article->image_brief) && ! $request->has('image_brief'))) {
            unset($validated['image_brief']);
        }
        if (! array_key_exists('faqs', $validated) || ($validated['faqs'] === null && ! empty($article->faqs) && ! $request->has('faqs'))) {
            unset($validated['faqs']);
        }

        if ($validated['status'] === 'published') {
            if (! $article->published_at) {
                $validated['published_at'] = now();
            }
        } elseif ($validated['status'] === 'draft') {
            $validated['published_at'] = null;
        }

        try {
            DB::beginTransaction();
            $article->update($validated);
            DB::commit();

            return redirect()->route('admin.articles.index')
                ->with('success', 'Artikel berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->withInput()->with('error', 'Gagal memperbarui artikel: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified article from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $article = Article::findOrFail($id);
        $article->delete();

        return redirect()->route('admin.articles.index')
            ->with('success', 'Artikel berhasil dihapus.');
    }

    /**
     * AI generation endpoint using Ollama — synchronous, no queue required.
     * Does NOT save half-baked records into database!
     */
    public function aiGenerate(
        Request $request,
        OllamaService $ollama,
        RegionalService $regionalService
    ): JsonResponse {
        $validated = $request->validate([
            'city_slug' => 'required|string',
            'district_slug' => 'nullable|string',
            // Word-count limits: 6,000 - 9,000 words inclusive (default: 7,500)
            'word_count' => 'required|integer|min:6000|max:9000',
            'topic' => 'nullable|string|max:255',
            // Optional service-aware generation: must reference a catalog entry.
            'service_slug' => ['nullable', 'string', Rule::in(array_keys($this->schemes->trainings()))],
        ]);

        $city = $regionalService->findCity($validated['city_slug']);
        if (! $city) {
            return response()->json([
                'success' => false,
                'error' => 'Wilayah kota/kabupaten tidak valid.',
            ], 422);
        }

        $district = null;
        if (! empty($validated['district_slug'])) {
            $district = $regionalService->findDistrict($validated['district_slug'], $validated['city_slug']);
        }

        $params = [
            'city_name' => $city['name'],
            'city_slug' => $city['slug'],
            'district_name' => $district['name'] ?? null,
            'district_slug' => $district['slug'] ?? null,
            'word_count' => $validated['word_count'],
            'topic' => $validated['topic'] ?? null,
            'service_slug' => $validated['service_slug'] ?? null,
        ];

        $result = $ollama->generateArticle($params);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Gagal menghasilkan artikel dari Ollama.',
            ], 500);
        }

        return response()->json($result);
    }

    /**
     * Handle image upload from Jodit or standalone upload.
     * Direct upload to public/uploads/articles without symlink.
     */
    public function uploadImage(Request $request, ImageUploadService $imageUploader): JsonResponse
    {
        // Support both single file and array of files from Jodit
        $file = $request->file('image')
            ?? $request->file('file')
            ?? ($request->file('files')[0] ?? null);

        if (! $file) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada file gambar yang diunggah.',
            ], 400);
        }

        $result = $imageUploader->storeImage($file);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], 422);
        }

        // Return Jodit-compatible JSON response
        return response()->json([
            'success' => true,
            'url' => $result['url'],
            'path' => $result['path'],
            'data' => [
                'url' => $result['url'],
                'path' => $result['path'],
            ],
            'files' => [$result['url']],
        ]);
    }
}
