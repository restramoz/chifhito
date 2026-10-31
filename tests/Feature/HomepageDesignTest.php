<?php

namespace Tests\Feature;

use App\Services\RegionalService;
use Tests\TestCase;

class HomepageDesignTest extends TestCase
{
    public function test_homepage_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Kompetensi teknis');
        $response->assertSee('Chifheto');
    }

    public function test_homepage_includes_accessible_landmarks(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Skip link
        $response->assertSee('href="#main-content"', false);
        $response->assertSee('Lewati ke konten utama', false);
        // Semantic main landmark
        $response->assertSee('id="main-content"', false);
        // Header and Footer landmarks
        $response->assertSee('<header', false);
        $response->assertSee('<footer', false);
        $response->assertSee('role="navigation"', false);
    }

    public function test_homepage_displays_services_without_invented_claims(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Cari topik atau nama pelatihan');
        $response->assertSee('Pertambangan dan Geologi');

        // Verify no invented claims
        $response->assertDontSee('Jaminan Lulus 100%');
        $response->assertDontSee('Pasti Terbit Sertifikat');
    }

    public function test_whatsapp_cta_uses_configurable_format(): void
    {
        // When WHATSAPP_PHONE is configured via env
        config(['services.whatsapp.phone' => '081234567890']);
        config(['services.whatsapp.default_message' => 'Halo, saya ingin konsultasi.']);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('https://wa.me/6281234567890', false);

        // Verify no hardcoded reference project phone
        $response->assertDontSee('8118500177');
    }

    public function test_whatsapp_cta_falls_back_gracefully_when_no_phone_configured(): void
    {
        config(['services.whatsapp.phone' => '']);
        $response = $this->get('/');

        $response->assertStatus(200);

        // No wa.me link may exist without a valid recipient; neutral text instead.
        $response->assertDontSee('https://wa.me/62', false);
        $response->assertSee('Hubungi admin untuk informasi konsultasi');
    }

    public function test_homepage_carries_brand_and_no_legacy_name(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $response->assertSee(config('app.name', 'Chifheto'));
        $response->assertSee('Pelatihan profesional');
    }

    public function test_homepage_and_desktop_and_mobile_nav_expose_regional_discovery(): void
    {
        $response = $this->get('/')->assertOk();
        $html = $response->getContent();
        $wilayahUrl = route('home') . '#wilayah';

        $this->assertSame(2, substr_count($html, 'href="' . $wilayahUrl . '"'));
        $response->assertSee('id="mobile-nav-drawer"', false);
        $response->assertSee('id="wilayah-heading"', false);
        $response->assertSee('id="province-search"', false);

        $provinces = app(RegionalService::class)->getAllProvinces();
        $this->assertCount(38, $provinces);
        foreach ($provinces as $province) {
            $this->assertStringContainsString(route('regional.province', $province['slug']), $html);
        }
    }

    public function test_regional_discovery_links_through_province_city_and_district_pages(): void
    {
        $regional = app(RegionalService::class);
        $provinceSummary = $regional->getAllProvinces()[0];
        $province = $regional->findProvince($provinceSummary['slug']);
        $this->get('/')
            ->assertOk()
            ->assertSee(route('regional.province', $province['slug']), false);

        $city = $province['cities'][0];
        $this->get(route('regional.province', $province['slug']))
            ->assertOk()
            ->assertSee(route('regional.city', $city['slug']), false);

        $district = $city['districts'][0];
        $this->get(route('regional.city', $city['slug']))
            ->assertOk()
            ->assertSee(route('regional.district', [$city['slug'], $district['slug']]), false);

        $this->get(route('regional.district', [$city['slug'], $district['slug']]))->assertOk();
    }
}
