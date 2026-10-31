<?php

namespace Tests\Feature;

use App\Services\SchemeService;
use Tests\TestCase;

class WhatsAppConversionFlowTest extends TestCase
{
    public function test_homepage_ctas_use_configured_phone_and_default_message(): void
    {
        config(['services.whatsapp.phone' => '081299887766']);
        config(['services.whatsapp.default_message' => 'Halo, saya ingin konsultasi mengenai program pelatihan profesional Chifheto.']);

        $response = $this->get('/');
        $response->assertStatus(200);

        $expectedUrl = 'https://wa.me/6281299887766?text=' . rawurlencode('Halo, saya ingin konsultasi mengenai program pelatihan profesional Chifheto.');

        // Hero CTA, Header CTA, Footer CTA, and Floating CTA should all include this URL
        $response->assertSee($expectedUrl, false);
    }

    public function test_homepage_ctas_render_non_link_contact_text_when_phone_is_missing(): void
    {
        config(['services.whatsapp.phone' => '']);
        config(['services.whatsapp.default_message' => 'Halo, saya ingin konsultasi mengenai program pelatihan profesional Chifheto.']);

        $response = $this->get('/');
        $response->assertStatus(200);

        // Regular site-wide CTAs degrade to non-link text when no recipient is configured.
        $response->assertDontSee('https://wa.me/62', false);

        // Neutral non-link contact copy is shown instead.
        $response->assertSee('Hubungi admin untuk informasi konsultasi');

        // Floating CTA still renders a universal (recipient-less) chat link, like reference.
        $response->assertSee('https://wa.me/?text=', false);
    }

    public function test_homepage_ctas_render_non_link_contact_text_when_phone_is_invalid(): void
    {
        config(['services.whatsapp.phone' => 'invalid-abc']);

        $response = $this->get('/');
        $response->assertStatus(200);

        $response->assertDontSee('https://wa.me/62', false);
        $response->assertSee('Hubungi admin untuk informasi konsultasi');
        $response->assertSee('https://wa.me/?text=', false);

        // Too-short digits are equally rejected (no half-baked wa.me/123 link)
        config(['services.whatsapp.phone' => '123']);
        $responseShort = $this->get('/');
        $responseShort->assertStatus(200);
        $responseShort->assertDontSee('https://wa.me/123', false);
    }

    public function test_program_detail_uses_program_specific_whatsapp_message(): void
    {
        config(['services.whatsapp.phone' => '081299887766']);

        $scheme = app(SchemeService::class)->find('teknisi-pelindian');
        $response = $this->get(route('program.show', 'teknisi-pelindian'));
        $response->assertStatus(200);
        $response->assertSee('https://wa.me/6281299887766?text=' . rawurlencode($scheme['wa_message']), false);
    }

    public function test_all_whatsapp_ctas_use_the_same_configuration_source(): void
    {
        // First configuration
        config(['services.whatsapp.phone' => '081234567891']);
        config(['services.whatsapp.default_message' => 'Pesan Tes Pertama']);

        $res1 = $this->get('/');
        $res1->assertStatus(200);
        $res1->assertSee('https://wa.me/6281234567891?text=' . rawurlencode('Pesan Tes Pertama'), false);

        // Dynamically change configuration source
        config(['services.whatsapp.phone' => '089876543210']);
        config(['services.whatsapp.default_message' => 'Pesan Tes Kedua']);

        $res2 = $this->get('/');
        $res2->assertStatus(200);
        $res2->assertSee('https://wa.me/6289876543210?text=' . rawurlencode('Pesan Tes Kedua'), false);
        $res2->assertDontSee('6281234567891', false);
    }

    public function test_floating_cta_has_accessible_markup_and_rel_attributes(): void
    {
        config(['services.whatsapp.phone' => '081299887766']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('aria-label="Layanan Bantuan WhatsApp"', false);
        $response->assertSee('target="_blank"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
        $response->assertSee('aria-label="Konsultasi cepat via WhatsApp (buka chat di tab baru)"', false);
    }

    public function test_floating_cta_renders_universal_link_when_no_valid_phone_is_configured(): void
    {
        config(['services.whatsapp.phone' => '']);

        $response = $this->get('/');
        $response->assertStatus(200);

        // The floating button remains rendered with a universal (recipient-less) chat link.
        $response->assertSee('aria-label="Konsultasi cepat via WhatsApp (buka chat di tab baru)"', false);
        $response->assertSee('https://wa.me/?text=', false);
    }

    public function test_consistent_phone_configuration_across_public_surfaces(): void
    {
        config(['services.whatsapp.phone' => '081290112233']);
        config(['services.whatsapp.default_message' => 'Konsultasi pertanian.']);

        $expectedUrl = 'https://wa.me/6281290112233?text=' . rawurlencode('Konsultasi pertanian.');
        $pages = [
            '/',
            route('program.show', 'teknisi-pelindian'),
            route('regional.province', 'jawa-barat'),
            route('regional.city', 'bandung'),
        ];

        foreach ($pages as $url) {
            $response = $this->get($url);
            $response->assertStatus(200);
            $response->assertSee($expectedUrl, false);
        }
    }

    public function test_no_real_contact_numbers_or_secrets_are_exposed(): void
    {
        // Override global config to a test number that is NOT a consultation number
        // so we can verify consultation numbers don't leak into global CTAs.
        config(['services.whatsapp.phone' => '081299887766']);
        config(['services.whatsapp.default_message' => 'Test message']);

        $pages = ['/', route('program.show', 'teknisi-pelindian'), route('program.show', 'teknisi-pelindian'), route('program.show', 'teknisi-pelindian')];

        foreach ($pages as $url) {
            $response = $this->get($url);
            $response->assertStatus(200);

            // Consultation numbers should NOT appear in global CTAs when global config is different.
            // They should only appear in article content (via addWhatsAppCallout) and the
            // article-consultation-numbers block.
            foreach (['8118500177', '81399810272', '87894580806', '87894580649'] as $number) {
                $response->assertDontSee($number);
            }

            // Global CTAs should use the configured phone (normalized)
            $response->assertSee('wa.me/6281299887766', false);

            // Never leak env markers
            $response->assertDontSee('WHATSAPP_PHONE=');
            $response->assertDontSee('APP_KEY=');
        }
    }
}
