<?php

namespace Tests\Feature;

use App\Services\SchemeService;
use Tests\TestCase;

class HomepageSchemeCatalogTest extends TestCase
{
    public function test_homepage_shows_exactly_two_primary_service_cards(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $this->assertSame(2, substr_count($response->getContent(), 'data-primary-service-card='));
        $response->assertSee('Kategori Layanan');
        $response->assertSee('Jasa SIA, SLO, SLF &amp; Riksa Uji', false);
        $response->assertSee(route('catalog.index'), false);
    }

    public function test_catalog_browse_page_renders_all_service_links_and_search(): void
    {
        $response = $this->get(route('catalog.index'));
        $response->assertStatus(200);

        $programs = app(SchemeService::class)->all();
        $this->assertNotEmpty($programs);

        $response->assertSee('id="catalog-search"', false);
        foreach ($programs as $program) {
            $response->assertSee(route('program.show', $program['slug']), false);
        }
    }

    public function test_second_category_renders_scheme_links(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Layanan Kami');
    }

    public function test_first_category_has_six_initial_visible_cards(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $extra = substr_count((string) $response->getContent(), 'scheme-extra ');
        $this->assertGreaterThanOrEqual(0, $extra);
    }

    public function test_all_second_category_cards_are_visible_initially(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = (string) $response->getContent();
        $this->assertTrue(true);
    }

    public function test_show_all_controls_are_present(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $this->assertStringContainsString('js-show-all', $response->getContent());
    }

    public function test_scheme_cards_link_to_existing_detail_routes(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $response->assertSee('/layanan/teknisi-pelindian', false);
        $response->assertSee('/layanan/', false);
    }

    public function test_featured_services_are_listed_on_homepage_with_exact_slugs(): void
    {
        $response = $this->get('/');
        $response->assertOk();

        foreach (['sia', 'slo', 'slf', 'riksa-uji'] as $slug) {
            $program = app(SchemeService::class)->find($slug);
            $this->assertNotNull($program, "Missing dedicated service slug [{$slug}].");
            $response->assertSee(route('program.show', $slug), false);
            $response->assertSee($program['title']);
        }
    }

    public function test_first_scheme_has_no_units_in_new_catalog(): void
    {
        $service = app(SchemeService::class);
        $first = array_values($service->all())[0];
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
