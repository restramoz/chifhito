<?php

namespace Tests\Feature;

use App\Services\RegionalService;
use Tests\TestCase;

class RegionalPagesTest extends TestCase
{
    /**
     * Test verified dataset integrity in RegionalService
     */
    public function test_regional_dataset_hierarchy_and_counts(): void
    {
        $service = app(RegionalService::class);

        $provinces = $service->getAllProvinces();
        $this->assertCount(38, $provinces, 'Dataset must have exactly 38 Indonesian provinces.');

        $cities = $service->getAllCities();
        $this->assertCount(514, $cities, 'Dataset must have exactly 514 Indonesian cities and regencies.');

        // Test sample province structure
        $dki = $service->findProvince('dki-jakarta');
        $this->assertNotNull($dki);
        $this->assertEquals('DKI Jakarta', $dki['name']);
        $this->assertEquals('Jawa', $dki['island']);
        $this->assertGreaterThan(0, count($dki['cities']));

        // Test sample city structure
        $bandung = $service->findCity('bandung');
        $this->assertNotNull($bandung);
        $this->assertEquals('Kota Bandung', $bandung['name']);
        $this->assertEquals('Jawa Barat', $bandung['province']);
        $this->assertGreaterThan(0, count($bandung['districts']));
    }

    /**
     * Test valid province landing page
     */
    public function test_province_page_renders_successfully(): void
    {
        config(['services.whatsapp.phone' => '081299887766']);

        $response = $this->get(route('regional.province', 'jawa-barat'));

        $response->assertStatus(200);
        $response->assertSee('Provinsi Jawa Barat');
        $response->assertSee('Pelatihan Pengolahan dan Pemurnian (Processing) Tambang');
        $response->assertSee('Kabupaten &amp; Kota di Provinsi Jawa Barat', false);

        // Breadcrumb
        $response->assertSee('aria-label="Breadcrumb"', false);
        $response->assertSee('Jawa Barat');

        // Program cards link to canonical program pages
        $response->assertSee(route('regional.province.program', ['province' => 'jawa-barat', 'program' => 'pelatihan-pengolahan-dan-pemurnian-processing-tambang']), false);

        // Program cards render full canonical names
        $response->assertSee('Pelatihan Pengolahan dan Pemurnian (Processing) Tambang');
        $response->assertSee('Pelatihan Pengolahan dan Pemurnian (Processing) Tambang');
        $response->assertSee('Pelatihan Pengolahan dan Pemurnian (Processing) Tambang', false);

        // WhatsApp CTA uses the central config and an agricultural message
        $response->assertSee('wa.me', false);
        $response->assertSee(rawurlencode('Halo, saya dari Provinsi Jawa Barat dan ingin konsultasi mengenai pelatihan teknis dan industri.'), false);

        // Province must be indexable (no "noindex")
        $response->assertDontSee('content="noindex');
    }

    /**
     * Regression: province page must not crash when $prog does not have a 'route' key.
     * All cards consistently provide valid named routes to canonical program pages.
     */
    public function test_province_page_program_cards_do_not_use_undefined_route_key(): void
    {
        // Any valid province slug is sufficient; the bug affects every province equally.
        foreach (['jawa-barat', 'jawa-timur', 'dki-jakarta'] as $slug) {
            $response = $this->get(route('regional.province', $slug));
            $response->assertStatus(200);
            $response->assertSee(route('regional.province.program', ['province' => $slug, 'program' => 'pelatihan-pengolahan-dan-pemurnian-processing-tambang']), false);
        }
    }

    /**
     * Test invalid province slug returns 404
     */
    public function test_invalid_province_slug_returns_404(): void
    {
        $response = $this->get(route('regional.province', 'provinsi-fiktif-123'));
        $response->assertStatus(404);
    }

    /**
     * Test valid city landing page
     */
    public function test_city_page_renders_successfully(): void
    {
        config(['services.whatsapp.phone' => '081299887766']);

        $response = $this->get(route('regional.city', 'bandung'));

        $response->assertStatus(200);
        $response->assertSee('Kota Bandung');
        $response->assertSee('Provinsi Jawa Barat');
        $response->assertSee('Kecamatan di Kota Bandung');
        $response->assertSee('Coblong');

        // Breadcrumbs & Parent Navigation
        $response->assertSee('aria-label="Breadcrumb"', false);
        $response->assertSee(route('regional.province', 'jawa-barat'), false);

        // Programs with full titles personalized for city
        $response->assertSee('Pelatihan Pengolahan dan Pemurnian (Processing) Tambang — Kota Bandung');
        $response->assertSee('Pelatihan Pengolahan dan Pemurnian (Processing) Tambang — Kota Bandung');
        $response->assertSee('Pelatihan Pengolahan dan Pemurnian (Processing) Tambang — Kota Bandung', false);

        // Program cards link to city-scoped program detail pages
        $response->assertSee(route('city.program.detail', ['city' => 'bandung', 'program' => 'pelatihan-pengolahan-dan-pemurnian-processing-tambang']), false);
        $response->assertSee(route('city.program.detail', ['city' => 'bandung', 'program' => 'pelatihan-pengolahan-dan-pemurnian-processing-tambang']), false);
        $response->assertSee(route('city.program.detail', ['city' => 'bandung', 'program' => 'pelatihan-pengolahan-dan-pemurnian-processing-tambang']), false);

        // WhatsApp CTA uses the central config and an agricultural message
        $response->assertSee('wa.me', false);
        $response->assertSee(rawurlencode('Halo, saya dari Kota Bandung dan ingin konsultasi mengenai pelatihan teknis dan industri.'), false);

        // Disclaimer / No-invented-locations policy
        $response->assertSee('Catatan penting:', false);
        $response->assertSee('Penyelenggaraan pelatihan fisik/in-house di Kota Bandung, jadwal pelaksanaan, dan asesmen kebutuhan dikonfirmasi langsung melalui representasi konsultan kami.', false);

        // City must be indexable (no "noindex")
        $response->assertDontSee('content="noindex');
    }

    /**
     * Test city slug alias canonical redirection (301)
     */
    public function test_city_alias_slugs_redirect_to_canonical(): void
    {
        // kota-bandung -> bandung (Kota)
        $response1 = $this->get(route('regional.city', 'kota-bandung'));
        $response1->assertRedirect(route('regional.city', 'bandung'), 301);

        // kabupaten-bandung -> kab-bandung (Kabupaten)
        $response2 = $this->get(route('regional.city', 'kabupaten-bandung'));
        $response2->assertRedirect(route('regional.city', 'kab-bandung'), 301);

        // simeulue -> kab-simeulue
        $response3 = $this->get(route('regional.city', 'simeulue'));
        $response3->assertRedirect(route('regional.city', 'kab-simeulue'), 301);
    }

    /**
     * Test invalid city slug returns 404
     */
    public function test_invalid_city_slug_returns_404(): void
    {
        $response = $this->get(route('regional.city', 'kota-khayalan-999'));
        $response->assertStatus(404);
    }

    /**
     * Test valid district landing page
     */
    public function test_district_page_renders_successfully(): void
    {
        config(['services.whatsapp.phone' => '081299887766']);

        $response = $this->get(route('regional.district', ['citySlug' => 'bandung', 'districtSlug' => 'coblong']));

        $response->assertStatus(200);
        $response->assertSee('Kecamatan Coblong');
        $response->assertSee('Kota Bandung');
        $response->assertSee('Jawa Barat');

        // Breadcrumbs & Parent Navigation
        $response->assertSee('aria-label="Breadcrumb"', false);
        $response->assertSee(route('regional.province', 'jawa-barat'), false);
        $response->assertSee(route('regional.city', 'bandung'), false);

        // Programs with full titles personalized for district (district pages show program title only)
        $response->assertSee('Pelatihan Pengolahan dan Pemurnian (Processing) Tambang');
        $response->assertSee('Pelatihan Pengolahan dan Pemurnian (Processing) Tambang');
        $response->assertSee('Pelatihan Pengolahan dan Pemurnian (Processing) Tambang', false);

        // Programs link to district-scoped training details.
        $response->assertSee(route('regional.district.program', ['citySlug' => 'bandung', 'districtSlug' => 'coblong', 'program' => 'pelatihan-pengolahan-dan-pemurnian-processing-tambang']), false);

        // Sibling districts navigation
        $response->assertSee('Kecamatan Lain di Kota Bandung');

        // WhatsApp CTA uses the central config and an agricultural message
        $response->assertSee('wa.me', false);
        $response->assertSee(rawurlencode('Halo, saya dari Kecamatan Coblong, Kota Bandung dan ingin konsultasi mengenai pelatihan teknis dan industri.'), false);

        // District pages are thin content and MUST be tagged noindex
        $response->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_regional_pages_render_no_wa_me_link_when_phone_is_empty(): void
    {
        config(['services.whatsapp.phone' => '']);

        foreach ([
            route('regional.province', 'jawa-barat'),
            route('regional.city', 'bandung'),
            route('regional.district', ['citySlug' => 'bandung', 'districtSlug' => 'coblong']),
        ] as $url) {
            $response = $this->get($url);
            $response->assertStatus(200);
            $response->assertDontSee('https://wa.me/62', false);
            $response->assertSee('Hubungi admin untuk informasi konsultasi');
        }
    }

    /**
     * Test that all three full program types resolve to their corresponding canonical content across regional levels
     */
    public function test_all_three_full_program_types_resolve_to_canonical_content_across_regional_levels(): void
    {
        // 1. Province level (e.g. Jawa Timur)
        $provResponse = $this->get(route('regional.province', 'jawa-timur'));
        $provResponse->assertStatus(200);
        $provResponse->assertSee('Pelatihan Pengolahan dan Pemurnian (Processing) Tambang');
        $provResponse->assertSee('Pelatihan Pengolahan dan Pemurnian (Processing) Tambang');
        $provResponse->assertSee('Pelatihan Pengolahan dan Pemurnian (Processing) Tambang', false);

        // 2. City level (e.g. Kota Malang)
        $cityResponse = $this->get(route('regional.city', 'malang'));
        $cityResponse->assertStatus(200);
        $cityResponse->assertSee('Pelatihan Pengolahan dan Pemurnian (Processing) Tambang — Kota Malang');
        $cityResponse->assertSee('Pelatihan Pengolahan dan Pemurnian (Processing) Tambang — Kota Malang');
        $cityResponse->assertSee('Pelatihan Pengolahan dan Pemurnian (Processing) Tambang — Kota Malang', false);

        // 3. District level (e.g. Kecamatan Wagir, Kabupaten Malang)
        $distResponse = $this->get(route('regional.district', ['citySlug' => 'kab-malang', 'districtSlug' => 'wagir']));
        $distResponse->assertStatus(200);
        $distResponse->assertSee('Pelatihan Pengolahan dan Pemurnian (Processing) Tambang');
        $distResponse->assertSee('Pelatihan Pengolahan dan Pemurnian (Processing) Tambang');
        $distResponse->assertSee('Pelatihan Pengolahan dan Pemurnian (Processing) Tambang', false);
    }

    /**
     * Test district alias city redirect
     */
    public function test_district_alias_city_redirects_to_canonical(): void
    {
        $response = $this->get(route('regional.district', ['citySlug' => 'kota-bandung', 'districtSlug' => 'coblong']));
        $response->assertRedirect(route('regional.district', ['citySlug' => 'bandung', 'districtSlug' => 'coblong']), 301);
    }

    /**
     * Test invalid district or mismatched city returns 404
     */
    public function test_invalid_district_returns_404(): void
    {
        // Invalid district in valid city
        $response1 = $this->get(route('regional.district', ['citySlug' => 'bandung', 'districtSlug' => 'kecamatan-tidak-ada']));
        $response1->assertStatus(404);

        // Valid district in wrong city (Coblong is in Bandung, not Surabaya)
        $response2 = $this->get(route('regional.district', ['citySlug' => 'surabaya', 'districtSlug' => 'coblong']));
        $response2->assertStatus(404);
    }

    public function test_city_and_district_training_details_show_context_content_related_links_and_consultation(): void
    {
        config(['services.whatsapp.phone' => '081299887766']);
        $slug = 'pelatihan-pengolahan-dan-pemurnian-processing-tambang';
        $title = 'Pelatihan Pengolahan dan Pemurnian (Processing) Tambang';
        $catalog = app(\App\Services\SchemeService::class);
        $related = $catalog->relatedTrainings($catalog->findTraining($slug));

        $cityUrl = route('regional.city.program', ['city' => 'bandung', 'program' => $slug]);
        $city = $this->get($cityUrl)->assertOk();
        $city->assertSee($title)->assertSee('Kota Bandung')->assertSee('Tujuan &amp; Ruang Lingkup', false);
        $city->assertSee('Siapa yang mungkin memerlukan topik ini?')->assertSee('Konteks wilayah');
        $city->assertSee('Kompetensi &amp; Output Kerja', false)->assertSee('Pokok Materi &amp; Tahapan Kerja', false);
        $city->assertSee('Jadwal Pelatihan')->assertSee('Belum dicantumkan');
        $city->assertSee('Canonical URL')->assertSee('<link rel="canonical" href="' . $cityUrl . '">', false);
        $city->assertSee(route('regional.province', 'jawa-barat'), false);
        $city->assertSee('https://wa.me/6281299887766?text=' . rawurlencode("Halo, saya dari Kota Bandung dan ingin konsultasi mengenai {$title}."), false);
        $this->assertNotEmpty($related);
        foreach ($related as $item) {
            $this->assertSame('ayana', $item['source']);
            $city->assertSee(route('regional.city.program', ['city' => 'bandung', 'program' => $item['slug']]), false);
        }

        $districtUrl = route('regional.district.program', ['citySlug' => 'bandung', 'districtSlug' => 'coblong', 'program' => $slug]);
        $district = $this->get($districtUrl)->assertOk();
        $district->assertSee('Kecamatan Coblong')->assertSee('Kota Bandung')->assertSee($title);
        $district->assertSee(route('regional.city', 'bandung'), false)->assertSee('FAQPage');
        preg_match('~<script type="application/ld\+json">(.*?)</script>~s', $district->getContent(), $schemaMatch);
        $faqSchema = json_decode($schemaMatch[1] ?? '', true);
        $this->assertSame('FAQPage', $faqSchema['@type'] ?? null);
        $district->assertSee('https://wa.me/6281299887766?text=' . rawurlencode("Halo, saya dari Kecamatan Coblong, Kota Bandung dan ingin konsultasi mengenai {$title}."), false);
        foreach ($related as $item) {
            $district->assertSee(route('regional.district.program', ['citySlug' => 'bandung', 'districtSlug' => 'coblong', 'program' => $item['slug']]), false);
        }
    }

    public function test_province_training_detail_links_back_into_the_regional_hierarchy(): void
    {
        $slug = 'pelatihan-pengolahan-dan-pemurnian-processing-tambang';
        $url = route('regional.province.program', ['province' => 'jawa-barat', 'program' => $slug]);
        $response = $this->get($url)->assertOk();
        $response->assertSee('Provinsi Jawa Barat')->assertSee('Pelatihan Pengolahan dan Pemurnian (Processing) Tambang');
        $response->assertSee(route('regional.city', 'kab-bogor'), false);
        $response->assertSee('<link rel="canonical" href="' . $url . '">', false);
    }

    /**
     * Test legacy compatibility URLs redirect with 301 to canonical regional URLs
     */
    public function test_legacy_regional_routes_redirect_to_canonical(): void
    {
        // /kota-{slug} -> /kota/{slug}
        $r1 = $this->get('/kota-bandung');
        $r1->assertRedirect(route('regional.city', 'bandung'), 301);

        // /pelatihan/kota-{slug} -> /kota/{slug}
        $r2 = $this->get('/pelatihan/kota-bandung');
        $r2->assertRedirect(route('regional.city', 'bandung'), 301);

        // /kota-{citySlug}/kecamatan-{districtSlug} -> /kecamatan/{citySlug}/{districtSlug}
        $r3 = $this->get('/kota-bandung/kecamatan-coblong');
        $r3->assertRedirect(route('regional.district', ['citySlug' => 'bandung', 'districtSlug' => 'coblong']), 301);

        // /kecamatan-{districtSlug} -> /kecamatan/{citySlug}/{districtSlug}
        $r4 = $this->get('/kecamatan-coblong');
        $r4->assertRedirect(route('regional.district', ['citySlug' => 'bandung', 'districtSlug' => 'coblong']), 301);

        // /kecamatan/{districtSlug} -> /kecamatan/{citySlug}/{districtSlug}
        $r5 = $this->get('/kecamatan/coblong');
        $r5->assertRedirect(route('regional.district', ['citySlug' => 'bandung', 'districtSlug' => 'coblong']), 301);

        // Unknown legacy kecamatan -> 404
        $r6 = $this->get('/kecamatan-fiktif-999');
        $r6->assertStatus(404);
    }
}
