<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomepageDesignTest extends TestCase
{
    public function test_homepage_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Teknisi Pelindian');
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
        $response->assertSee('Teknisi Pelindian');
        $response->assertSee('teknisi-pelindian');
        $response->assertSee('Teknisi Pelindian');

        // Verify no stale SIO/alat-berat references on homepage
        $response->assertDontSee('Sertifikasi SIO');
        $response->assertDontSee('Surat Ijin Operator');
        $response->assertDontSee('Permenaker');
        $response->assertDontSee('Jasa Audit');

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

        $response->assertSee(config('app.name', 'Najkaltare'));
        $response->assertDontSee('Training K3 Listrik');
        $response->assertDontSee('K3 Listrik');
    }
}
