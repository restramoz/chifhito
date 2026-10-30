<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OllamaService;
use App\Services\RegionalService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiGenerateArticleTest extends TestCase
{
    use RefreshDatabase;

    /** Bind a fake OllamaService that returns the given response array. */
    protected function bindFakeOllama(array $response): void
    {
        $mock = $this->createMock(OllamaService::class);
        $mock->method('generateArticle')
            ->willReturn($response);
        $this->app->instance(OllamaService::class, $mock);
    }

    protected function bindFakeRegional(array $city): void
    {
        $mock = $this->createMock(RegionalService::class);
        $mock->method('findCity')
            ->willReturn($city);
        // district not used in these tests
        $mock->method('findDistrict')
            ->willReturn(null);
        $this->app->instance(RegionalService::class, $mock);
    }

    public function test_rejects_word_count_below_minimum(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->bindFakeRegional(['name' => 'Test City', 'slug' => 'some-city']);
        $this->actingAs($admin)->postJson(route('admin.articles.ai-generate'), [
            'city_slug' => 'some-city',
            'word_count' => 5000, // below 6000
        ])->assertStatus(422);
    }

    public function test_rejects_word_count_above_maximum(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->bindFakeRegional(['name' => 'Test City', 'slug' => 'some-city']);
        $this->actingAs($admin)->postJson(route('admin.articles.ai-generate'), [
            'city_slug' => 'some-city',
            'word_count' => 14000, // above 9000
        ])->assertStatus(422);
    }

    /**
     * Root-cause regression test: generation must return article data directly in the HTTP
     * response — no job_id, no polling required. Previously the controller dispatched a
     * background queue job, which never ran without a worker and left the browser polling forever.
     */
    public function test_successful_generation_returns_article_data_synchronously(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->bindFakeRegional(['name' => 'Test City', 'slug' => 'some-city']);
        $this->bindFakeOllama([
            'success' => true,
            'title' => 'Pelatihan K3 Listrik di Test City',
            'slug' => 'pelatihan-k3-listrik-test-city',
            'excerpt' => 'Ringkasan singkat artikel.',
            'content' => '<p>Isi artikel.</p>',
            'seo_title' => 'Pelatihan K3 Listrik | Training K3 Listrik',
            'meta_description' => 'Deskripsi meta artikel.',
            'suggested_faqs' => [],
            'image_brief' => ['concept' => 'Foto industri', 'english_prompt' => 'photo', 'aspect_ratio' => '16:9', 'recommended_alt' => 'alt'],
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.articles.ai-generate'), [
            'city_slug' => 'some-city',
            'word_count' => 7500,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['success', 'title', 'slug', 'content'])
            ->assertJson(['success' => true]);

        // No job_id in response — caller should not need to poll
        $this->assertArrayNotHasKey('job_id', $response->json());
        // No job_id means no polling route is required either
        $this->assertArrayNotHasKey('status', $response->json());
    }

    public function test_dedicated_service_slugs_are_accepted_for_article_generation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->bindFakeRegional(['name' => 'Test City', 'slug' => 'some-city']);

        $serviceSlugs = ['sia', 'slo', 'slf', 'riksa-uji'];
        $ollama = $this->createMock(OllamaService::class);
        $ollama->expects($this->exactly(count($serviceSlugs)))
            ->method('generateArticle')
            ->with($this->callback(fn (array $params): bool => in_array($params['service_slug'] ?? null, $serviceSlugs, true)))
            ->willReturn(['success' => true, 'title' => 'Draft', 'slug' => 'draft', 'content' => '<p>Konten</p>']);
        $this->app->instance(OllamaService::class, $ollama);

        foreach ($serviceSlugs as $serviceSlug) {
            $this->actingAs($admin)
                ->postJson(route('admin.articles.ai-generate'), [
                    'city_slug' => 'some-city',
                    'word_count' => 7500,
                    'service_slug' => $serviceSlug,
                ])
                ->assertOk()
                ->assertJson(['success' => true]);
        }
    }

    public function test_generation_failure_returns_500_with_error(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->bindFakeRegional(['name' => 'Test City', 'slug' => 'some-city']);
        $this->bindFakeOllama([
            'success' => false,
            'error' => 'Koneksi ke Ollama gagal.',
        ]);

        $this->actingAs($admin)->postJson(route('admin.articles.ai-generate'), [
            'city_slug' => 'some-city',
            'word_count' => 7500,
        ])->assertStatus(500)->assertJson(['success' => false, 'error' => 'Koneksi ke Ollama gagal.']);
    }

    public function test_rejects_invalid_city_slug(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // Regional returns null (city not found)
        $mock = $this->createMock(RegionalService::class);
        $mock->method('findCity')->willReturn(null);
        $this->app->instance(RegionalService::class, $mock);

        $this->actingAs($admin)->postJson(route('admin.articles.ai-generate'), [
            'city_slug' => 'nonexistent-city',
            'word_count' => 7500,
        ])->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_requires_authentication(): void
    {
        $this->postJson(route('admin.articles.ai-generate'), [
            'city_slug' => 'some-city',
            'word_count' => 7500,
        ])->assertStatus(401);
    }

    public function test_requires_admin(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->postJson(route('admin.articles.ai-generate'), [
            'city_slug' => 'some-city',
            'word_count' => 7500,
        ])->assertStatus(403);
    }
}
