<?php

namespace Tests\Unit;

use App\Support\WhatsApp;
use Tests\TestCase;

class WhatsAppTest extends TestCase
{
    public function test_normalizes_standard_indonesian_phone_numbers(): void
    {
        $this->assertEquals('6281234567890', WhatsApp::normalizePhone('081234567890'));
        $this->assertEquals('6281234567890', WhatsApp::normalizePhone('+62 812-3456-7890'));
        $this->assertEquals('6281234567890', WhatsApp::normalizePhone('6281234567890'));
        $this->assertEquals('6281234567890', WhatsApp::normalizePhone('62 812 3456 7890'));
        $this->assertEquals('6281234567890', WhatsApp::normalizePhone('62081234567890'));
    }

    public function test_handles_missing_or_empty_phone_configuration(): void
    {
        $this->assertNull(WhatsApp::normalizePhone(null));
        $this->assertNull(WhatsApp::normalizePhone(''));
        $this->assertNull(WhatsApp::normalizePhone('   '));
    }

    public function test_handles_invalid_phone_configuration_safely(): void
    {
        $this->assertNull(WhatsApp::normalizePhone('invalid'));
        $this->assertNull(WhatsApp::normalizePhone('abc'));
        $this->assertNull(WhatsApp::normalizePhone('--++--'));
        $this->assertNull(WhatsApp::normalizePhone('0'));
        $this->assertNull(WhatsApp::normalizePhone('123'));
        $this->assertNull(WhatsApp::normalizePhone('08123')); // < 8 digits after normalization
        $this->assertNull(WhatsApp::normalizePhone('6281234567890123456789')); // > 15 digits
    }

    public function test_generates_valid_url_with_configured_phone(): void
    {
        config(['services.whatsapp.phone' => '081234567890']);
        config(['services.whatsapp.default_message' => 'Halo, saya ingin konsultasi.']);

        $url = WhatsApp::url();
        $this->assertEquals('https://wa.me/6281234567890?text=Halo%2C%20saya%20ingin%20konsultasi.', $url);
    }

    public function test_replaces_only_the_legacy_site_wide_mining_default_message(): void
    {
        config(['services.whatsapp.default_message' => 'Halo, saya ingin konsultasi mengenai skema kompetensi pertambangan, mineral, batubara, dan energi.']);
        $this->assertSame('Halo, saya ingin konsultasi mengenai program pelatihan profesional Chifheto.', WhatsApp::defaultMessage());

        config(['services.whatsapp.default_message' => 'Konsultasi pelatihan tambang untuk tim kami.']);
        $this->assertSame('Konsultasi pelatihan tambang untuk tim kami.', WhatsApp::defaultMessage());
    }

    public function test_url_returns_null_when_phone_is_missing(): void
    {
        config(['services.whatsapp.phone' => '']);
        config(['services.whatsapp.default_message' => 'Halo, saya ingin konsultasi.']);

        $url = WhatsApp::url();
        $this->assertNull($url);
    }

    public function test_url_returns_null_when_phone_is_invalid(): void
    {
        config(['services.whatsapp.phone' => 'invalid-phone-123']);
        config(['services.whatsapp.default_message' => 'Halo, saya ingin konsultasi.']);

        $url = WhatsApp::url();
        $this->assertNull($url);
    }

    public function test_url_never_produces_a_link_without_a_recipient_number(): void
    {
        foreach (['', null, ' ', 'abc', '!!!'] as $phone) {
            config(['services.whatsapp.phone' => $phone]);
            $url = WhatsApp::url('Pesan konsultasi.');
            $this->assertNull($url);
        }
    }

    public function test_fallback_text_reads_config_when_no_url_is_available(): void
    {
        config(['services.whatsapp.fallback_text' => 'Hubungi admin untuk informasi konsultasi.']);
        $this->assertEquals('Hubungi admin untuk informasi konsultasi.', WhatsApp::fallbackText());

        // Missing key falls back to the neutral default text.
        config(['services.whatsapp' => []]);
        $this->assertEquals('Hubungi admin untuk informasi konsultasi.', WhatsApp::fallbackText());
    }

    public function test_url_encodes_indonesian_text_and_special_punctuation(): void
    {
        $message = "Halo, saya ingin tanya: jadwal & biaya pelatihan grup 1/2 K3 Listrik?";
        $url = WhatsApp::url($message, '081234567890');

        $expectedEncoded = rawurlencode($message);
        $this->assertEquals("https://wa.me/6281234567890?text={$expectedEncoded}", $url);

        // Verify specific characters are encoded according to RFC 3986
        $this->assertStringContainsString('%2C', $url); // ,
        $this->assertStringContainsString('%3A', $url); // :
        $this->assertStringContainsString('%20', $url); // space
        $this->assertStringContainsString('%26', $url); // &
        $this->assertStringContainsString('%2F', $url); // /
        $this->assertStringContainsString('%3F', $url); // ?
    }
}
