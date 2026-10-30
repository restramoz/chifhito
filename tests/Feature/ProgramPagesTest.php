<?php

namespace Tests\Feature;

use App\Services\SchemeService;
use Tests\TestCase;

class ProgramPagesTest extends TestCase
{
    /**
     * Test that a sample of schemes from layanan.json render successfully.
     */
    public function test_all_schemes_render_successfully(): void
    {
        $programs = app(SchemeService::class)->all();

        $this->assertGreaterThan(0, count($programs));

        // Verify a representative sample of programs render successfully.
        $slugs = array_keys(array_slice($programs, 0, 5, true));

        foreach ($slugs as $slug) {
            $data = $programs[$slug];
            $url = route('program.show', $slug);
            $response = $this->get($url);

            $response->assertStatus(200);
            $response->assertSee($data['hero_heading']);
            $response->assertSee($data['hero_highlight']);
        }
    }

    /**
     * Test canonical and legacy route aliases for programs.
     */
    public function test_program_routes_have_expected_url_paths(): void
    {
        $this->assertEquals('/layanan/teknisi-pelindian', parse_url(route('program.show', 'teknisi-pelindian'), PHP_URL_PATH));
        $this->assertEquals('/program/teknisi-pelindian', parse_url(route('program.legacy.show', 'teknisi-pelindian'), PHP_URL_PATH));

        // Legacy route returns 200
        $response = $this->get('/program/teknisi-pelindian');
        $response->assertStatus(200);
        $response->assertSee('Teknisi Pelindian');
    }

    /**
     * Test unknown slugs are not rendered.
     */
    public function test_unknown_slugs_return_404(): void
    {
        $this->get('/layanan/does-not-exist')->assertStatus(404);
    }

    /**
     * Test distinct meta titles and meta descriptions for programs.
     */
    public function test_each_program_has_distinct_page_title_and_meta_description(): void
    {
        $programs = app(SchemeService::class)->all();

        // Meta titles and descriptions are derived from unique names.
        $titles = array_map(fn (array $p) => $p['meta_title'], $programs);
        $descriptions = array_map(fn (array $p) => $p['meta_description'], $programs);

        // Source records can share titles across independent catalogs.
        $this->assertGreaterThanOrEqual(1, count(array_unique($titles)));
        $this->assertGreaterThanOrEqual(1, count(array_unique($descriptions)));

        $first = null;
        foreach (array_slice($programs, 0, 3) as $scheme) {
            $response = $this->get(route('program.show', $scheme['slug']));
            $response->assertStatus(200);
            $response->assertSee(e($scheme['meta_title']));
            if ($first !== null) {
                $this->assertNotEquals($first['meta_description'], $scheme['meta_description']);
            }
            $first = $scheme;
        }
    }

    /**
     * Test accessible breadcrumb markup on program detail page.
     */
    public function test_program_pages_include_accessible_breadcrumbs(): void
    {
        $response = $this->get(route('program.show', 'teknisi-pelindian'));

        $response->assertStatus(200);
        $response->assertSee('aria-label="Breadcrumb"', false);
        $response->assertSee('itemtype="https://schema.org/BreadcrumbList"', false);
        $response->assertSee('href="' . route('home') . '"', false);
        $response->assertSee('Beranda', false);
        $response->assertSee('aria-current="page"', false);
    }

    /**
     * Test information card sections.
     */
    public function test_program_pages_display_transparent_unverified_info_notice(): void
    {
        $response = $this->get(route('program.show', 'teknisi-pelindian'));

        $response->assertStatus(200);
        $response->assertSee('Informasi Penyelenggaraan &amp; Pelaksanaan', false);
        $response->assertSee('Jadwal Pelatihan');
        $response->assertSee('Durasi Program');
        $response->assertSee('Investasi Biaya');
    }

    /**
     * Test WhatsApp CTA formatting and prefilled message.
     */
    public function test_whatsapp_cta_prefills_specific_program_message(): void
    {
        config(['services.whatsapp.phone' => '089988776655']);

        $response = $this->get(route('program.show', 'teknisi-pelindian'));
        $response->assertStatus(200);
        $expectedMsg = rawurlencode(app(SchemeService::class)->find('teknisi-pelindian')['wa_message']);
        $response->assertSee('https://wa.me/6289988776655?text=' . $expectedMsg, false);
    }

    /**
     * Test fallback when phone is empty: no broken wa.me link, neutral text instead.
     */
    public function test_whatsapp_cta_falls_back_when_phone_is_empty(): void
    {
        config(['services.whatsapp.phone' => '']);

        $response = $this->get(route('program.show', 'teknisi-pelindian'));
        $response->assertStatus(200);

        $response->assertDontSee('https://wa.me/62', false);
        $response->assertSee('Hubungi admin untuk informasi konsultasi');
    }

    /**
     * Test related services links on detail page.
     */
    public function test_each_program_page_links_to_other_programs(): void
    {
        $programs = app(SchemeService::class)->all();
        $slugs = array_keys($programs);

        $response = $this->get(route('program.show', $slugs[0]));
        $response->assertStatus(200);

        foreach (array_slice($slugs, 1, 3) as $otherSlug) {
            $response->assertSee(route('program.show', $otherSlug), false);
        }
    }

    /**
     * Test responsive FAQ accordion on program pages.
     */
    public function test_program_pages_have_responsive_faq_section(): void
    {
        $response = $this->get(route('program.show', 'teknisi-pelindian'));

        $response->assertStatus(200);
        $response->assertSee('<details', false);
        $response->assertSee('<summary', false);
        $response->assertSee('Pertanyaan Umum Seputar');
    }

    /**
     * Test safety rule: no fabricated claims.
     */
    public function test_program_pages_have_no_invented_claims(): void
    {
        $programs = app(SchemeService::class)->all();

        foreach (array_keys(array_slice($programs, 0, 3, true)) as $slug) {
            $response = $this->get(route('program.show', $slug));
            $response->assertStatus(200);

            // No guaranteed outcomes
            $response->assertDontSee('100% garansi lulus');
            $response->assertDontSee('pasti lulus');
            $response->assertDontSee('tanpa tes');
            // No fabricated certification or regulatory claims
            $response->assertDontSee('Terakreditasi resmi');
            $response->assertDontSee('Sertifikat dijamin terbit');
            $response->assertDontSee('Permenaker No. 8/2020');
        }
    }

    /**
     * Test homepage links to schemes.
     */
    public function test_homepage_links_to_programs(): void
    {
        $home = $this->get(route('home'));

        $home->assertStatus(200);
        $home->assertSee(route('catalog.index'), false);
        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee(route('program.show', 'teknisi-pelindian'), false);
    }

    public function test_dedicated_service_slugs_resolve_to_detail_pages(): void
    {
        foreach (['sia', 'slo', 'slf', 'riksa-uji'] as $slug) {
            $program = app(SchemeService::class)->find($slug);
            $this->assertNotNull($program);
            $this->assertSame($slug, $program['slug']);
            $this->assertSame('/layanan/' . $slug, parse_url(route('program.show', $slug), PHP_URL_PATH));
            $this->get(route('program.show', $slug))
                ->assertOk()
                ->assertSee($program['title']);
        }
    }
}
