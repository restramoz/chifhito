<?php

namespace Tests\Unit;

use App\Services\OllamaService;
use App\Services\SchemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OllamaPromptTest extends TestCase
{
    use RefreshDatabase;

    private function fakeSuccessfulOllama(): void
    {
        config([
            'services.ollama.base_url' => 'http://dummy-ollama.local',
            'services.ollama.model' => 'dummy-model',
        ]);

        $content = str_repeat('kata ', 7500);
        Http::fake(['*' => Http::response([
            'message' => ['content' => json_encode([
                'title' => 'Judul Test',
                'slug' => 'judul-test',
                'excerpt' => 'Excerpt test',
                'content' => $content,
                'seo_title' => 'SEO Title Test | ' . config('app.name', 'Najkaltare'),
                'meta_description' => 'Meta Description Test',
                'suggested_faqs' => [],
                'image_brief' => [],
            ])],
        ], 200)]);
    }

    public function test_prompts_carry_brand_and_global_context(): void
    {
        $this->fakeSuccessfulOllama();
        $brand = config('app.name', 'Najkaltare');

        (new OllamaService())->generateArticle([
            'city_name' => 'Bandung',
            'city_slug' => 'bandung',
            'word_count' => 7500,
        ]);

        Http::assertSent(function (object $request) use ($brand): bool {
            $system = $request['messages'][0]['content'];
            $user = $request['messages'][1]['content'];

            $prompts = $request['messages'][0]['content'] . ' ' . $request['messages'][1]['content'];

            return str_contains($system, $brand)
                && str_contains($user, 'Cakupan layanan: SIA, SLO, SLF, Riksa Uji')
                && str_contains($prompts, 'pelatihan, sertifikasi, inspeksi')
                && !str_contains($prompts, 'Jucerdyamg');
        });
    }

    public function test_prompt_uses_configured_brand_and_selected_service_naturally(): void
    {
        config(['app.name' => 'Najkaltare Uji']);
        $this->fakeSuccessfulOllama();

        (new OllamaService())->generateArticle([
            'city_name' => 'Bandung',
            'city_slug' => 'bandung',
            'word_count' => 7500,
            'service_slug' => 'sia',
        ]);

        Http::assertSent(function (object $request): bool {
            $system = $request['messages'][0]['content'];
            $user = $request['messages'][1]['content'];

            return str_contains($system, 'Najkaltare Uji')
                && str_contains($system, 'Program spesifik: SIA')
                && str_contains($user, 'Program Spesifik: SIA')
                && !str_contains($system . $user, 'Jucerdyamg');
        });
    }

    public function test_fallback_text_uses_the_dynamic_brand(): void
    {
        config(['app.name' => 'Najkaltare Dinamis']);

        $result = (new OllamaService())->parseResponse(
            '<p>Konten fallback yang tidak berbentuk JSON.</p>',
            'Panduan layanan industri',
            'Bandung',
            7500
        );

        $this->assertStringContainsString('Najkaltare Dinamis', $result['seo_title']);
        $this->assertStringContainsString('Najkaltare Dinamis', $result['image_brief']['recommended_alt']);
        $this->assertStringNotContainsString('Jucerdyamg', json_encode($result));
    }

    public function test_legacy_brand_in_model_response_is_normalized(): void
    {
        $this->fakeSuccessfulOllama();

        $result = (new OllamaService())->parseResponse(json_encode([
            'title' => 'Panduan Jucerdyamg',
            'slug' => 'panduan-jucerdyamg',
            'excerpt' => 'Panduan Jucerdyamg untuk layanan industri.',
            'content' => '<p>Konsultasikan layanan Jucerdyamg.</p>',
            'seo_title' => 'Panduan Jucerdyamg',
            'meta_description' => 'Layanan Jucerdyamg.',
            'suggested_faqs' => [['q' => 'Apa itu Jucerdyamg?', 'a' => 'Layanan Jucerdyamg.']],
            'image_brief' => ['recommended_alt' => 'Aktivitas Jucerdyamg'],
        ]), 'Fallback', 'Bandung', 7500);

        $encoded = json_encode($result);
        $this->assertStringContainsString(config('app.name', 'Najkaltare'), $encoded);
        $this->assertStringNotContainsString('Jucerdyamg', $encoded);
        $this->assertStringContainsString('<p>Konsultasikan layanan ' . config('app.name', 'Najkaltare') . '.</p>', $result['content']);
    }

    public function test_prompts_exclude_legacy_identifiers(): void
    {
        $this->fakeSuccessfulOllama();

        (new OllamaService())->generateArticle([
            'city_name' => 'Bandung',
            'city_slug' => 'bandung',
            'word_count' => 7500,
        ]);

        Http::assertSent(function (object $request): bool {
            $prompts = $request['messages'][0]['content'] . ' ' . $request['messages'][1]['content'];

            return !str_contains($prompts, 'K3 Listrik')
                && !str_contains($prompts, 'k3-listrik')
                && !str_contains($prompts, 'Keselamatan Kelistrikan');
        });
    }

    public function test_service_slug_injects_targeted_service_context(): void
    {
        $this->fakeSuccessfulOllama();

        $scheme = (new SchemeService())->find('teknisi-pelindian');
        $this->assertNotNull($scheme);
        $title = (string) $scheme['title'];

        (new OllamaService())->generateArticle([
            'city_name' => 'Bandung',
            'city_slug' => 'bandung',
            'word_count' => 7500,
            'service_slug' => 'teknisi-pelindian',
        ]);

        Http::assertSent(function (object $request) use ($title): bool {
            $system = $request['messages'][0]['content'];
            $user = $request['messages'][1]['content'];

            return str_contains($system, 'Program spesifik: ' . $title)
                && str_contains($user, '- Program Spesifik: ' . $title);
        });
    }
}
