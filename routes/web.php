<?php

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ServiceCatalogController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\PublicArticleController;
use App\Http\Controllers\RegionalController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

// Homepage
Route::get('/', function () {
    $latestArticles = \Illuminate\Support\Facades\Schema::hasTable('articles')
        ? \App\Models\Article::published()->orderBy('published_at', 'desc')->take(3)->get()
        : collect();

    return view('home', compact('latestArticles'));
})->name('home');

// Searchable directory for the complete public service catalog.
Route::get('/katalog-layanan', [ServiceCatalogController::class, 'index'])->name('catalog.index');

// XML Sitemap for Search Engines
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// robots.txt (dynamic, ensures absolute Sitemap URL for crawlers)
Route::get('/robots.txt', function () {
    $baseUrl = rtrim((string) config('app.url'), '/');
$content = "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /admin/\nDisallow: /login\nDisallow: /kecamatan/\n\nSitemap: {$baseUrl}/sitemap.xml\n";
    return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('robots');

// 13 Services & Training Programs
Route::get('/layanan/{slug}', [ProgramController::class, 'show'])
    ->name('program.show')
    ->where('slug', '[a-z0-9\-]+');

// Legacy route alias for /program/{slug}
Route::get('/program/{slug}', [ProgramController::class, 'show'])
    ->name('program.legacy.show')
    ->where('slug', '[a-z0-9\-]+');

// City-specific service detail pages preserving regional context
Route::get('/kota/{city}/layanan/{program}', [ProgramController::class, 'cityProgram'])
    ->name('city.program.detail');

Route::get('/kota/{city}/program/{program}', [ProgramController::class, 'cityProgram'])
    ->name('city.program.legacy');

// Contextual Ayana training pages within the existing regional hierarchy.
Route::get('/provinsi/{province}/pelatihan/{program}', [ProgramController::class, 'provinceTraining'])
    ->name('regional.province.program')
    ->where(['province' => '[a-z0-9\-]+', 'program' => '[a-z0-9\-]+']);
Route::get('/kota/{city}/pelatihan/{program}', [ProgramController::class, 'cityTraining'])
    ->name('regional.city.program')
    ->where(['city' => '[a-z0-9\-]+', 'program' => '[a-z0-9\-]+']);
Route::get('/kecamatan/{citySlug}/{districtSlug}/pelatihan/{program}', [ProgramController::class, 'districtTraining'])
    ->name('regional.district.program')
    ->where(['citySlug' => '[a-z0-9\-]+', 'districtSlug' => '[a-z0-9\-]+', 'program' => '[a-z0-9\-]+']);

// Public Articles
Route::get('/artikel', [PublicArticleController::class, 'index'])->name('articles.index');
Route::get('/artikel/{slug}', [PublicArticleController::class, 'show'])->name('articles.show');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Panel Routes (Protected by Auth & Admin verification)
Route::middleware(['auth', EnsureUserIsAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminArticleController::class, 'index'])->name('dashboard');
        Route::resource('articles', AdminArticleController::class)->except(['show']);
        Route::post('articles/ai-generate', [AdminArticleController::class, 'aiGenerate'])->name('articles.ai-generate');
        Route::post('upload-image', [AdminArticleController::class, 'uploadImage'])->name('upload.image');
    });

// Regional landing pages — slug-validated against BPS dataset, never arbitrary filesystem paths
Route::get('/provinsi/{slug}', [RegionalController::class, 'province'])
    ->name('regional.province')
    ->where('slug', '[a-z0-9\-]+');

Route::get('/kota/{slug}', [RegionalController::class, 'city'])
    ->name('regional.city')
    ->where('slug', '[a-z0-9\-]+');

// District pages are noindex (see regional/district.blade.php); include but keep separate
Route::get('/kecamatan/{citySlug}/{districtSlug}', [RegionalController::class, 'district'])
    ->name('regional.district')
    ->where(['citySlug' => '[a-z0-9\-]+', 'districtSlug' => '[a-z0-9\-]+']);

// Legacy compatibility redirects (from reference project URL structures)
Route::get('/kota-{slug}', [RegionalController::class, 'legacyCityRedirect'])
    ->name('city.landing')
    ->where('slug', '[a-z0-9\-]+');
Route::get('/pelatihan/kota-{slug}', [RegionalController::class, 'legacyCityRedirect'])
    ->where('slug', '[a-z0-9\-]+');
Route::get('/kota-{citySlug}/kecamatan-{districtSlug}', [RegionalController::class, 'legacyNestedDistrictRedirect'])
    ->name('district.show')
    ->where(['citySlug' => '[a-z0-9\-]+', 'districtSlug' => '[a-z0-9\-]+']);
Route::get('/kecamatan-{districtSlug}', [RegionalController::class, 'legacyDistrictRedirect'])
    ->name('kecamatan.landing')
    ->where('districtSlug', '[a-z0-9\-]+');
Route::get('/pelatihan/kecamatan-{districtSlug}', [RegionalController::class, 'legacyDistrictRedirect'])
    ->where('districtSlug', '[a-z0-9\-]+');
Route::get('/kecamatan/{districtSlug}', [RegionalController::class, 'legacyDistrictRedirect'])
    ->where('districtSlug', '[a-z0-9\-]+');
