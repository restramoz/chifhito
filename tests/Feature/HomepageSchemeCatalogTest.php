<?php

namespace Tests\Feature;

use App\Services\SchemeService;
use Tests\TestCase;

class HomepageSchemeCatalogTest extends TestCase
{
    public function test_homepage_shows_editorial_training_categories_and_discovery(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Mulai dari topiknya.');
        $response->assertSee('Pertambangan dan Geologi');
        $response->assertSee('Minyak, Gas, dan Pemboran');
        $response->assertSee(route('catalog.index'), false);
    }

    public function test_catalog_browse_page_renders_all_training_links_and_search(): void
    {
        $response = $this->get(route('catalog.index'));
        $response->assertStatus(200);

        $programs = app(SchemeService::class)->trainings();
        $this->assertNotEmpty($programs);

        $response->assertSee('id="catalog-search"', false);
        foreach ($programs as $program) {
            $response->assertSee(route('program.show', $program['slug']), false);
        }
    }

    public function test_training_catalog_is_source_attributed_and_searchable(): void
    {
        $response = $this->get(route('catalog.index'));
        $response->assertStatus(200);
        $response->assertSee('PELATIHAN');
        $response->assertSee('id="catalog-search"', false);
        $this->get(route('program.show', 'pelatihan-pengolahan-dan-pemurnian-processing-tambang'))
            ->assertSee('list_pelatihan_ayana.json');
    }

    public function test_category_rows_link_to_the_catalog_filter(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $response->assertSee(route('catalog.index', ['category' => 'Pertambangan dan Geologi']), false);
    }

    public function test_all_second_category_cards_are_visible_initially(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $response->assertSee('Diskusikan kebutuhan pelatihan');
    }

    public function test_show_all_controls_are_present(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $response->assertSee('Lihat seluruh katalog');
    }

    public function test_legacy_service_and_training_detail_routes_remain_available(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $this->get(route('program.show', 'teknisi-pelindian'))->assertOk();
        $this->get(route('program.show', 'pelatihan-pengolahan-dan-pemurnian-processing-tambang'))->assertOk();
    }

    public function test_existing_featured_service_detail_routes_remain_available(): void
    {
        $response = $this->get('/');
        $response->assertOk();

        foreach (['sia', 'slo', 'slf', 'riksa-uji'] as $slug) {
            $program = app(SchemeService::class)->find($slug);
            $this->assertNotNull($program, "Missing dedicated service slug [{$slug}].");
            $this->get(route('program.show', $slug))->assertOk();
        }
    }

    public function test_first_scheme_has_no_units_in_new_catalog(): void
    {
        $service = app(SchemeService::class);
        $first = array_values($service->trainings())[0];
        $this->assertSame(0, count($first['units']));
        $this->assertTrue(is_array($first['curriculum_topics']));
    }

    public function test_image_assets_resolve(): void
    {
        foreach (['hero.webp','agribisnis.webp','lsp.webp','training.webp','consult.webp'] as $file) {
            $this->assertFileExists(public_path('images/home/' . $file));
        }
    }
}
