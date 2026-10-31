<?php

namespace Tests\Unit;

use App\Services\OllamaService;
use App\Services\SchemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class K3ListrikServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_service_lookup_remains_available_without_entering_training_catalog(): void
    {
        $service = app(SchemeService::class);

        $this->assertCount(318, $service->trainings());
        $this->assertNotContains('layanan', array_column($service->trainings(), 'source'));
        // Retain the old detail route for compatibility.
        $this->assertNotNull($service->find('teknisi-pelindian'));
        $this->assertSame('Teknisi Pelindian', $service->find('teknisi-pelindian')['title']);
    }

    public function test_pick_topic_uses_a_service_from_the_catalog(): void
    {
        config([
            'services.ollama.base_url' => 'http://dummy-ollama.local',
            'services.ollama.model' => 'dummy-model',
        ]);

        $selected = (new OllamaService())->pickTopicForRegion('Bandung', 'bandung');

        $this->assertArrayHasKey('topic', $selected);
        $this->assertArrayHasKey('focus', $selected);
        $this->assertNotEmpty($selected['topic']);
        $this->assertNotEmpty($selected['focus']);
        $titles = array_column(app(SchemeService::class)->trainings(), 'title');
        $this->assertContains($selected['topic'], $titles);
    }

    public function test_presentation_grouping_uses_catalog_categories_and_keeps_every_service_once(): void
    {
        $service = app(SchemeService::class);
        $groups = $service->groupedForPresentation();

        $total = count($service->trainings());
        $mergedCount = count(array_merge(...array_values($groups)));
        $this->assertSame($total, $mergedCount);
        $this->assertArrayHasKey('Pertambangan dan Geologi', $groups);
        $this->assertArrayHasKey('Minyak, Gas, dan Pemboran', $groups);
    }
}
