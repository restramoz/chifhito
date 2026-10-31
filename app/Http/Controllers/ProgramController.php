<?php

namespace App\Http\Controllers;

use App\Services\RegionalService;
use App\Services\RegionalTrainingContentService;
use App\Services\SchemeService;
use Illuminate\Contracts\View\View;

class ProgramController extends Controller
{
    public function __construct(private readonly SchemeService $schemes) {}

    /**
     * Display the specified program or service page by slug.
     */
    public function show(string $slug): View
    {
        $program = $this->schemes->findTraining($slug) ?? $this->schemes->findLegacyService($slug);

        if ($program === null) {
            abort(404, 'Layanan atau program pelatihan tidak ditemukan.');
        }

        if (($program['source'] ?? '') === 'ayana') {
            return $this->trainingDetailView(
                $program,
                null,
                [],
                $this->schemes->relatedTrainings($program, 4),
                $program['wa_message'],
                'program.show',
                ['slug' => $program['slug']],
                app(RegionalTrainingContentService::class)
            );
        }

        // Recommend related schemes from the same authoritative catalog
        $catalog = ($program['source'] ?? '') === 'ayana'
            ? $this->schemes->trainings()
            : $this->schemes->all('layanan');
        $relatedPrograms = collect($catalog)
            ->filter(fn (array $p, string $key): bool => $key !== $program['slug'])
            ->values()
            ->take(6)
            ->all();

        return view('programs.show', [
            'program' => $program,
            'relatedPrograms' => $relatedPrograms,
            'otherPrograms' => $relatedPrograms,
        ]);
    }

    /**
     * Display a city-specific service detail page preserving regional context.
     *
     * @param string $city      Slug of the city/regency (e.g. "bandung", "kab-bekasi")
     * @param string $program   Catalog slug (e.g. "perunggasan-jenjang-5")
     */
    public function cityProgram(string $city, string $program, RegionalService $regional): View
    {
        $cityData = $regional->findCity($city);
        abort_if($cityData === null, 404, 'Kota/Kabupaten tidak ditemukan.');

        $programData = $this->schemes->findTraining($program) ?? $this->schemes->findLegacyService($program);

        abort_if($programData === null, 404, 'Layanan tidak ditemukan.');

        if (($programData['source'] ?? '') === 'ayana') {
            $province = $regional->findProvince($cityData['province_slug']);

            return $this->trainingDetailView(
                $programData,
                ['type' => 'city', 'label' => $cityData['name'], 'province' => $province, 'city' => $cityData],
                $this->cityRegionLinks($cityData),
                $this->schemes->relatedTrainings($programData, 4),
                "Halo, saya dari {$cityData['name']} dan ingin konsultasi mengenai {$programData['title']}.",
                'regional.city.program',
                ['city' => $cityData['slug'], 'program' => $programData['slug']],
                app(RegionalTrainingContentService::class)
            );
        }

        $relatedPrograms = collect($this->schemes->all('layanan'))
            ->filter(fn (array $p, string $key): bool => $key !== $programData['slug'])
            ->values()
            ->take(6)
            ->all();

        $waMessage = "Halo, saya dari {$cityData['name']} dan ingin konsultasi mengenai {$programData['title']}.";

        return view('city.program-detail', [
            'city' => $cityData,
            'program' => $programData,
            'relatedPrograms' => $relatedPrograms,
            'otherPrograms' => $relatedPrograms,
            'waMessage' => $waMessage,
        ]);
    }

    public function provinceTraining(string $province, string $program, RegionalService $regional): View
    {
        $provinceData = $regional->findProvince($province);
        abort_if($provinceData === null, 404, 'Provinsi tidak ditemukan.');
        if ($province !== $provinceData['slug']) {
            abort(404);
        }

        $programData = $this->schemes->findTraining($program);
        abort_if($programData === null, 404, 'Pelatihan tidak ditemukan.');

        $links = collect($provinceData['cities'])->take(3)->map(fn (array $city): array => [
            'label' => $city['name'],
            'url' => route('regional.city', $city['slug']),
        ])->all();

        return $this->trainingDetailView(
            $programData,
            ['type' => 'province', 'label' => 'Provinsi ' . $provinceData['name'], 'province' => $provinceData],
            $links,
            $this->schemes->relatedTrainings($programData, 4),
            "Halo, saya dari Provinsi {$provinceData['name']} dan ingin konsultasi mengenai {$programData['title']}.",
            'regional.province.program',
            ['province' => $provinceData['slug'], 'program' => $programData['slug']],
            app(RegionalTrainingContentService::class)
        );
    }

    public function cityTraining(string $city, string $program, RegionalService $regional): View
    {
        $cityData = $regional->findCity($city);
        abort_if($cityData === null, 404, 'Kota/Kabupaten tidak ditemukan.');
        if ($city !== $cityData['slug']) {
            abort(404);
        }

        $programData = $this->schemes->findTraining($program);
        abort_if($programData === null, 404, 'Pelatihan tidak ditemukan.');
        $province = $regional->findProvince($cityData['province_slug']);

        return $this->trainingDetailView(
            $programData,
            ['type' => 'city', 'label' => $cityData['name'], 'province' => $province, 'city' => $cityData],
            $this->cityRegionLinks($cityData),
            $this->schemes->relatedTrainings($programData, 4),
            "Halo, saya dari {$cityData['name']} dan ingin konsultasi mengenai {$programData['title']}.",
            'regional.city.program',
            ['city' => $cityData['slug'], 'program' => $programData['slug']],
            app(RegionalTrainingContentService::class)
        );
    }

    public function districtTraining(string $citySlug, string $districtSlug, string $program, RegionalService $regional): View
    {
        $district = $regional->findDistrict($districtSlug, $citySlug);
        abort_if($district === null, 404, 'Kecamatan tidak ditemukan.');
        if ($citySlug !== $district['city_slug'] || $districtSlug !== $district['slug']) {
            abort(404);
        }

        $programData = $this->schemes->findTraining($program);
        abort_if($programData === null, 404, 'Pelatihan tidak ditemukan.');
        $province = $regional->findProvince($district['province_slug']);
        $links = [
            ['label' => 'Provinsi ' . $district['province'], 'url' => route('regional.province', $district['province_slug'])],
            ['label' => $district['city_name'], 'url' => route('regional.city', $district['city_slug'])],
        ];
        foreach (array_slice($district['sibling_districts'], 0, 2) as $sibling) {
            $links[] = [
                'label' => 'Kecamatan ' . $sibling['name'],
                'url' => route('regional.district', [$district['city_slug'], $sibling['slug']]),
            ];
        }

        return $this->trainingDetailView(
            $programData,
            [
                'type' => 'district',
                'label' => 'Kecamatan ' . $district['name'],
                'province' => $province,
                'city' => $district['city'],
                'district' => $district,
            ],
            $links,
            $this->schemes->relatedTrainings($programData, 4),
            "Halo, saya dari Kecamatan {$district['name']}, {$district['city_name']} dan ingin konsultasi mengenai {$programData['title']}.",
            'regional.district.program',
            ['citySlug' => $district['city_slug'], 'districtSlug' => $district['slug'], 'program' => $programData['slug']],
            app(RegionalTrainingContentService::class)
        );
    }

    private function cityRegionLinks(array $city): array
    {
        $links = [[
            'label' => 'Provinsi ' . $city['province'],
            'url' => route('regional.province', $city['province_slug']),
        ], [
            'label' => $city['name'],
            'url' => route('regional.city', $city['slug']),
        ]];

        foreach (array_slice($city['districts'], 0, 3) as $district) {
            $links[] = [
                'label' => 'Kecamatan ' . $district['name'],
                'url' => route('regional.district', [$city['slug'], $district['slug']]),
            ];
        }

        return $links;
    }

    private function trainingDetailView(
        array $program,
        ?array $region,
        array $regionalLinks,
        array $relatedPrograms,
        string $waMessage,
        string $detailRoute,
        array $detailRouteParams,
        RegionalTrainingContentService $content
    ): View {
        $breadcrumbs = $region === null
            ? [[
                'label' => $program['category_label'],
                'url' => route('catalog.index', ['category' => $program['category_label']]),
            ]]
            : [['label' => 'Wilayah', 'url' => route('home') . '#wilayah']];
        if ($region !== null && isset($region['province'])) {
            $breadcrumbs[] = [
                'label' => 'Provinsi ' . $region['province']['name'],
                'url' => route('regional.province', $region['province']['slug']),
            ];
        }
        if ($region !== null && isset($region['city'])) {
            $breadcrumbs[] = [
                'label' => $region['city']['name'],
                'url' => route('regional.city', $region['city']['slug']),
            ];
        }
        if ($region !== null && isset($region['district'])) {
            $breadcrumbs[] = [
                'label' => 'Kecamatan ' . $region['district']['name'],
                'url' => null,
            ];
        }
        $breadcrumbs[] = ['label' => $program['title'], 'url' => null];

        return view('programs.training-detail', [
            'program' => $program,
            'region' => $region,
            'regionalLinks' => $regionalLinks,
            'relatedPrograms' => $relatedPrograms,
            'pageContent' => $content->for($program, $region),
            'breadcrumbs' => $breadcrumbs,
            'waMessage' => $waMessage,
            'detailRoute' => $detailRoute,
            'detailRouteParams' => $detailRouteParams,
        ]);
    }
}
