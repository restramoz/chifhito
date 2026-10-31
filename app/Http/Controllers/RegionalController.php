<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\RegionalService;
use App\Services\SchemeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegionalController extends Controller
{
    public function __construct(private readonly RegionalService $regional, private readonly SchemeService $schemes) {}

    /**
     * Province landing page: /provinsi/{slug}
     */
    public function province(Request $request, string $slug): View
    {
        $province = $this->regional->findProvince($slug);

        abort_if($province === null, 404, 'Provinsi tidak ditemukan.');

        $waMessage = "Halo, saya dari Provinsi {$province['name']} dan ingin konsultasi mengenai pelatihan teknis dan industri.";

        return view('regional.province', [
            'province' => $province,
            'programs' => $this->schemes->trainings(),
            'waMessage' => $waMessage,
        ]);
    }

    /**
     * City/Kabupaten landing page: /kota/{slug}
     */
    public function city(Request $request, string $slug): View|RedirectResponse
    {
        $city = $this->regional->findCity($slug);

        abort_if($city === null, 404, 'Kota/Kabupaten tidak ditemukan.');

        // Canonical: redirect to the proper slug if user hit an alias
        if ($slug !== $city['slug']) {
            // shortcut: redirect, when to upgrade → use a permanent redirect middleware for cleaner SEO
            return redirect()->route('regional.city', $city['slug'], 301);
        }

        $nearbyCities = $this->regional->getProvinceCities($city['province_code'], $city['code'], 8);

        $articles = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('articles')) {
            $articles = Article::published()
                ->where('target_city_slug', $slug)
                ->latest('published_at')
                ->take(6)
                ->get();
        }

        $waMessage = "Halo, saya dari {$city['name']} dan ingin konsultasi mengenai pelatihan teknis dan industri.";

        return view('regional.city', [
            'city' => $city,
            'nearbyCities' => $nearbyCities,
            'programs' => $this->schemes->trainings(),
            'articles' => $articles,
            'waMessage' => $waMessage,
        ]);
    }

    /**
     * District landing page: /kecamatan/{citySlug}/{districtSlug}
     *
     * District pages are intentionally thin and tagged noindex until
     * meaningful content (like schedules or local info) is available.
     */
    public function district(Request $request, string $citySlug, string $districtSlug): View|RedirectResponse
    {
        $district = $this->regional->findDistrict($districtSlug, $citySlug);

        abort_if($district === null, 404, 'Kecamatan tidak ditemukan.');

        // Canonical: redirect to the proper city slug if user hit an alias
        if ($citySlug !== $district['city_slug']) {
            return redirect()->route('regional.district', [$district['city_slug'], $district['slug']], 301);
        }

        $articles = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('articles')) {
            $articles = Article::published()
                ->where('target_city_slug', $district['city_slug'])
                ->latest('published_at')
                ->take(6)
                ->get();
        }

        $waMessage = "Halo, saya dari Kecamatan {$district['name']}, {$district['city_name']} dan ingin konsultasi mengenai pelatihan teknis dan industri.";

        return view('regional.district', [
            'district' => $district,
            'programs' => $this->schemes->trainings(),
            'articles' => $articles,
            'waMessage' => $waMessage,
        ]);
    }

    /**
     * Legacy compatibility redirect: /kota-{slug} or /pelatihan/kota-{slug} -> /kota/{slug}
     */
    public function legacyCityRedirect(string $slug): RedirectResponse
    {
        return redirect()->route('regional.city', $slug, 301);
    }

    /**
     * Legacy compatibility redirect: /kota-{citySlug}/kecamatan-{districtSlug} -> /kecamatan/{citySlug}/{districtSlug}
     */
    public function legacyNestedDistrictRedirect(string $citySlug, string $districtSlug): RedirectResponse
    {
        return redirect()->route('regional.district', ['citySlug' => $citySlug, 'districtSlug' => $districtSlug], 301);
    }

    /**
     * Legacy standalone district redirect: /kecamatan-{slug} or /kecamatan/{slug} -> /kecamatan/{citySlug}/{districtSlug}
     */
    public function legacyDistrictRedirect(string $districtSlug): RedirectResponse
    {
        $district = $this->regional->findDistrict($districtSlug);
        abort_if($district === null, 404, 'Kecamatan tidak ditemukan.');

        return redirect()->route('regional.district', [
            'citySlug' => $district['city_slug'],
            'districtSlug' => $district['slug'],
        ], 301);
    }
}
