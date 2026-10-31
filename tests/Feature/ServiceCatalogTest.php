<?php

namespace Tests\Feature;

use App\Services\SchemeService;
use Tests\TestCase;

class ServiceCatalogTest extends TestCase
{
    /** Training discovery must only expose records from the Ayana source. */
    public function test_catalog_loads_all_unique_titles_from_ayana_only(): void
    {
        $service = app(SchemeService::class);
        $source = json_decode(file_get_contents(base_path('list_pelatihan_ayana.json')), true);
        $expected = array_values(array_unique($source));
        $trainings = $service->trainings();

        $this->assertCount(318, $trainings);
        $this->assertSame($expected, array_column($trainings, 'title'));
        $this->assertSame('list_pelatihan_ayana.json', $service->trainingSources()['ayana']['file']);
        $this->assertSame(['ayana'], array_values(array_unique(array_column($trainings, 'source'))));
        $this->assertSame(['list_pelatihan_ayana.json'], array_values(array_unique(array_column($trainings, 'source_file'))));
    }

    public function test_general_catalog_displays_every_unique_source_title_and_all_categories(): void
    {
        $response = $this->get(route('catalog.index'))->assertOk();
        $rawTitles = json_decode(file_get_contents(base_path('list_pelatihan_ayana.json')), true);
        $titles = array_values(array_unique($rawTitles));
        $response->assertSee('318 judul');

        preg_match_all('/<li data-catalog-item>/', $response->getContent(), $items);
        $this->assertCount(318, $items[0]);
        foreach ($titles as $title) {
            $response->assertSee($title);
        }

        $service = app(SchemeService::class);
        $categories = $service->trainingCategories();
        $this->assertCount(8, $categories);
        $this->assertSame(318, array_sum(array_map('count', $categories)));
        foreach ($categories as $label => $programs) {
            $response->assertSee($label);
            $response->assertSee(count($programs) . ' judul');
        }
    }

    public function test_every_scheme_has_complete_required_structure(): void
    {
        foreach (app(SchemeService::class)->trainings() as $slug => $scheme) {
            foreach (['title', 'meta_title', 'meta_description', 'hero_heading', 'hero_highlight', 'hero_subheading', 'wa_message', 'introduction', 'curriculum_topics', 'training_info', 'faqs'] as $key) {
                $this->assertArrayHasKey($key, $scheme, "Scheme {$slug} is missing [{$key}].");
            }

            $this->assertArrayHasKey('curriculum_topics', $scheme);
            $this->assertSame([], $scheme['faqs']);
            $this->assertSame([], $scheme['target_audience']);
            $this->assertSame([], $scheme['roles_and_responsibilities']);
            $this->assertSame('training', $scheme['category']);
            $this->assertLessThanOrEqual(3, count($scheme['faqs']), "Scheme {$slug} may have at most 3 FAQs.");
            $this->assertSame(
                ['jadwal', 'durasi', 'investasi', 'persyaratan', 'otorisasi'],
                array_keys($scheme['training_info']),
                "Scheme {$slug} training_info keys changed."
            );
        }
    }

    public function test_service_titles_meta_and_wa_messages_are_unique(): void
    {
        $catalog = app(SchemeService::class)->trainings();
        $slugs = array_keys($catalog);
        $this->assertSame(count($slugs), count(array_unique($slugs)));
        $this->assertSame(318, count(array_unique(array_column($catalog, 'title'))));
    }

    public function test_catalog_filters_distinguish_training_and_search_source_titles(): void
    {
        $training = $this->get('/katalog-layanan?type=training&q=Storage+Tank');
        $training->assertOk();
        $training->assertSee('Storage Tank Oil &amp; Gas', false);
        $training->assertDontSee('SIA');

        $category = $this->get('/katalog-layanan?category=Pertambangan+dan+Geologi');
        $category->assertOk();
        $category->assertSee('Pelatihan Pengolahan dan Pemurnian');
        $category->assertSee('Pertambangan dan Geologi');
        $category->assertSee('aria-current="page"', false);
        $category->assertSee('30 judul');
        $category->assertDontSee('Storage Tank Oil &amp; Gas', false);
        $this->get('/katalog-layanan?q=Storage+Tank')->assertSee('Storage Tank Oil &amp; Gas', false);
        $legacySearch = $this->get('/katalog-layanan?q=Teknisi+Pelindian');
        $legacySearch->assertOk()->assertSee('Tidak ada pelatihan yang cocok.');
        preg_match_all('/<li data-catalog-item>/', $legacySearch->getContent(), $legacyItems);
        $this->assertCount(0, $legacyItems[0]);
    }

    public function test_search_reset_and_empty_state_are_server_rendered_from_the_complete_catalog(): void
    {
        $search = $this->get('/katalog-layanan?q=Storage+Tank')->assertOk();
        preg_match_all('/<li data-catalog-item>/', $search->getContent(), $matches);
        $this->assertCount(1, $matches[0]);
        $search->assertSee('Hapus filter');
        $search->assertSee('value="Storage Tank"', false);

        $empty = $this->get('/katalog-layanan?q=tidak-ada-topik-ini')->assertOk();
        $empty->assertSee('Tidak ada pelatihan yang cocok.');
        $empty->assertSee(route('catalog.index'), false);
        preg_match_all('/<li data-catalog-item>/', $empty->getContent(), $emptyItems);
        $this->assertCount(0, $emptyItems[0]);

        $reset = $this->get(route('catalog.index'))->assertOk();
        preg_match_all('/<li data-catalog-item>/', $reset->getContent(), $resetItems);
        $this->assertCount(318, $resetItems[0]);
    }

    public function test_representative_catalog_links_resolve_to_actual_training_details(): void
    {
        $trainings = array_values(app(SchemeService::class)->trainings());
        foreach ([$trainings[0], $trainings[array_key_last($trainings)]] as $training) {
            $this->get(route('program.show', $training['slug']))
                ->assertOk()
                ->assertSee($training['title']);
        }
    }

    public function test_sitemap_lists_ayana_training_urls_and_keeps_regional_urls(): void
    {
        $xml = $this->get('/sitemap.xml')->assertOk();
        $xml->assertSee(route('program.show', 'pelatihan-pengolahan-dan-pemurnian-processing-tambang'), false);
        $xml->assertDontSee(route('program.show', 'teknisi-pelindian'), false);
        $xml->assertSee(route('regional.province', 'jawa-barat'), false);
        $xml->assertSee(route('regional.city', 'bandung'), false);
    }
}
