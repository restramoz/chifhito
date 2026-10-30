<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Loads the five authoritative mining/energy scheme catalogs and exposes them
 * as one unified program catalog with source attribution.
 *
 * Source files are read-only: records are adapted, not rewritten. Names,
 * unit codes/names, lsp values and counts are preserved verbatim.
 */
class SchemeService
{
    /**
     * Authoritative source files, in a fixed display order.
     */
    protected const SOURCE_FILES = [
        'layanan' => 'layanan.json',
    ];

    protected const SOURCE_IMAGES = [
        'k3listrik' => '/images/home/sumber-teknisi.jpg',
    ];

    /**
     * Descriptive Indonesian alt text specific to each category image.
     */
    protected const SOURCE_IMAGE_ALTS = [
        'k3listrik' => 'Teknisi K3 Listrik dengan helm keselamatan di dekat panel listrik dan instalasi kelistrikan industri',
    ];

    /**
     * Homepage presentation groupings for the unified catalog. Display-only
     * buckets matched against service names; names, slugs and records stay
     * untouched. Evaluation order matters: verbs-and-tools, then installation,
     * then automation, then safety/roles, anything unmatched falls to
     * Maintenance & Pengukuran.
     */
    protected const PRESENTATION_GROUPS = [
        'maintenance-pengukuran' => ['maintenance', 'pemeliharaan', 'pengoperasian', 'testing', 'commissioning', 'troubleshooting', 'multimeter', 'alat ukur'],
        'instalasi-proteksi' => ['instalasi', 'inslatasi', 'proteksi', 'grounding', 'wiring', 'distribution', 'panel', 'switchgear', 'switchboard', 'circuit breaker', 'short circuit', 'overcurrent', 'transformator', 'penerangan', 'pju', 'sel surya', 'air conditioning', 'gedung', 'arus kuat', 'komponen listrik'],
        'otomasi-industri' => ['plc', 'scada', 'otomasi', 'elektronika', 'elektro', 'arus lemah'],
        'k3-listrik' => ['k3', 'osha', 'arc flash', 'arc blast', 'shock', 'kebakaran', 'bahaya', 'alat pelindung', 'helm', 'sarung tangan', 'gfci', 'inspektur'],
    ];

    /**
     * Role keywords for the group fallback (Maintenance & Pengukuran).
     */
    protected const PRESENTATION_ROLES = ['engineer', 'technician', 'teknisi', 'operator', 'supervisor', 'manager', 'praktisi', 'pemula'];

    protected ?array $programs = null;

    protected ?array $sources = null;

    /**
     * Cached adapters for single files loaded ad-hoc.
     */
    protected array $cache = [];

    public function all(?string $source = null, array $filters = []): array
    {
        $programs = $this->load();

        if ($source !== null && $source !== '') {
            $filters['source'] = $source;
        }

        if ($filters === []) {
            return $programs;
        }

        return $this->filterLoaded($programs, $filters);
    }

    public function sources(): array
    {
        if ($this->sources === null) {
            $this->load();
        }

        return $this->sources ?? [];
    }

    /**
     * Group the unified catalog into the four homepage presentation buckets.
     * Purely presentational: keyed by each program's slug, order preserved.
     *
     * @return array<string, array<string, array>> bucket => slug => program
     */
    public function groupedForPresentation(): array
    {
        $groups = [
            'maintenance-pengukuran' => [],
            'instalasi-proteksi' => [],
            'otomasi-industri' => [],
            'k3-listrik' => [],
        ];

        foreach ($this->load() as $slug => $program) {
            $t = ' ' . mb_strtolower((string) $program['title']) . ' ';

            if (self::matchesAny(self::PRESENTATION_GROUPS['maintenance-pengukuran'], $t)) {
                $bucket = 'maintenance-pengukuran';
            } elseif (self::matchesAny(self::PRESENTATION_GROUPS['instalasi-proteksi'], $t)) {
                $bucket = 'instalasi-proteksi';
            } elseif (self::matchesAny(self::PRESENTATION_GROUPS['otomasi-industri'], $t)) {
                $bucket = 'otomasi-industri';
            } elseif (self::matchesAny(self::PRESENTATION_GROUPS['k3-listrik'], $t)) {
                $bucket = 'k3-listrik';
            } elseif (self::matchesAny(self::PRESENTATION_ROLES, $t)) {
                $bucket = 'maintenance-pengukuran';
            } else {
                $bucket = 'maintenance-pengukuran';
            }

            $groups[$bucket][$slug] = $program;
        }

        return $groups;
    }

    /**
     * Substring match on the padded lowercase title.
     *
     * @param array<int, string> $keywords
     */
    protected static function matchesAny(array $keywords, string $lowercasedTitle): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($lowercasedTitle, ' ' . $keyword)) {
                return true;
            }
        }

        return false;
    }

    public function getSource(string $key): ?array
    {
        return $this->sources()[$key] ?? null;
    }

    public function find(string $identifier): ?array
    {
        $all = $this->load();

        // Accept stable identifiers such as "source:slug".
        if (str_contains($identifier, ':')) {
            [$source, $slug] = explode(':', $identifier, 2);
            $slug = Str::slug($slug);
            if (isset($all[$slug]) && (($all[$slug]['source'] ?? null) === $source || ($all[$slug]['source_key'] ?? null) === $source)) {
                return $all[$slug];
            }
        }

        $key = Str::slug($identifier);

        return $all[$key] ?? null;
    }

    public function exists(string $identifier): bool
    {
        return $this->find($identifier) !== null;
    }

    public function bySource(string $source): array
    {
        return $this->all($source);
    }

    public function filterBySource(string $source): array
    {
        return $this->bySource($source);
    }

    public function schemesBySource(string $source): array
    {
        return $this->bySource($source);
    }

    public function filterByCategory(string $category): array
    {
        return $this->all($category);
    }

    public function filter(array $filters = []): array
    {
        return $this->all(null, $filters);
    }

    public function search(string $query): array
    {
        return $this->all(null, ['q' => $query]);
    }

    public function validateSelection(string $identifier): bool
    {
        return $this->exists($identifier);
    }

    public function sourceOf(string $slug): ?string
    {
        $record = $this->find($slug);

        return $record['source'] ?? $record['source_key'] ?? null;
    }

    protected function load(?string $path = null): array
    {
        // Ad-hoc single-file load, used by tests and validation tooling.
        if ($path !== null) {
            return $this->loadFile($path, 'file');
        }

        if ($this->programs !== null) {
            return $this->programs;
        }

        $programs = [];
        $sources = [];

        foreach (self::SOURCE_FILES as $key => $file) {
            $full = base_path($file);
            $raw = is_file($full) ? file_get_contents($full) : false;

            if ($raw === false) {
                throw new \RuntimeException('Unable to read scheme source file: ' . $file);
            }

            $decoded = json_decode($raw, true);

            if (!is_array($decoded)) {
                throw new \RuntimeException('Invalid JSON in scheme source file: ' . $file);
            }

            [$sourceMeta, $adapted] = $this->adaptSource($key, $file, $decoded);

            $adapted = array_values(array_filter(
                $adapted,
                static fn (array $scheme): bool => !self::isExcludedService((string) ($scheme['name'] ?? ''))
            ));

            // Merge adapted schemes, preserving deterministic slug collisions
            // across the unified catalog (-2, -3, ...).
            foreach ($adapted as $scheme) {
                $name = trim((string) ($scheme['name'] ?? ''));
                if ($name === '') {
                    throw new \RuntimeException('Scheme source ' . $file . ' contains a scheme without a name.');
                }
                if (!isset($scheme['_slug'])) {
                    // Should never happen, but keeps the index deterministic.
                    $scheme['_slug'] = Str::slug($name);
                }
                $base = $scheme['_slug'];
                $slug = $base;
                $suffix = 2;
                while (isset($programs[$slug])) {
                    $slug = $base . '-' . $suffix;
                    $suffix++;
                }
                unset($scheme['_slug']);
                $program = $this->program($slug, $name, $scheme, $sourceMeta);
                $programs[$slug] = $program;
            }

            $sources[$key] = $sourceMeta;
        }

        // Sort nothing else; keep source-file order then source record order.
        $this->programs = $programs;
        $this->sources = $sources;

        return $this->programs;
    }

    /**
     * Load a single scheme file as a one-source catalog. Used for explicit
     * validation and tests.
     */
    protected function loadFile(string $path, string $fallbackSource): array
    {
        $default = $path === base_path('schemes.json');

        if ($default && $this->programs !== null) {
            return $this->programs;
        }

        $raw = is_file($path) ? file_get_contents($path) : false;

        if ($raw === false) {
            throw new \RuntimeException('Unable to read schemes file: ' . basename($path));
        }

        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            throw new \RuntimeException('Invalid JSON in schemes file: ' . basename($path));
        }

        $sourceKey = $fallbackSource;
        $sourceFile = basename($path);
        [$sourceMeta, $adapted] = $this->adaptSource($sourceKey, $sourceFile, $decoded);

        $adapted = array_values(array_filter(
            $adapted,
            static fn (array $scheme): bool => !self::isExcludedService((string) ($scheme['name'] ?? ''))
        ));

        $programs = [];
        foreach ($adapted as $scheme) {
            $name = trim((string) ($scheme['name'] ?? ''));
            if ($name === '') {
                throw new \RuntimeException('Scheme source ' . $sourceFile . ' contains a scheme without a name.');
            }
            $base = Str::slug($name);
            $slug = $base;
            $suffix = 2;
            while (isset($programs[$slug])) {
                $slug = $base . '-' . $suffix;
                $suffix++;
            }
            $programs[$slug] = $this->program($slug, $name, $scheme, $sourceMeta);
        }

        if ($default) {
            $this->programs = $programs;
        }

        return $programs;
    }

    /**
     * Explicit adapter layer. Each source file is validated and normalized into
     * a common intermediate shape with source attribution attached.
     *
     * @return array{0:array,1:array} source metadata and adapted scheme list
     */
    protected function adaptSource(string $sourceKey, string $sourceFile, array $decoded): array
    {
        // Support layanan.json, a top-level list of category objects.
        if (array_is_list($decoded) && isset($decoded[0]) && is_array($decoded[0]) && isset($decoded[0]['layanan'])) {
            $schemes = [];
            foreach ($decoded as $categoryIndex => $category) {
                if (!is_array($category)) {
                    throw new \RuntimeException('Scheme source ' . $sourceFile . ' contains a non-object category at index ' . $categoryIndex . '.');
                }

                $categoryName = trim((string) ($category['kategori'] ?? ''));
                if ($categoryName === '') {
                    throw new \RuntimeException('Scheme source ' . $sourceFile . ' contains a category without "kategori" at index ' . $categoryIndex . '.');
                }

                if (!isset($category['layanan']) || !is_array($category['layanan'])) {
                    throw new \RuntimeException('Scheme source ' . $sourceFile . ' is missing the "layanan" list for category ' . $categoryName . '.');
                }

                foreach (array_values($category['layanan']) as $serviceIndex => $serviceName) {
                    if (!is_string($serviceName)) {
                        throw new \RuntimeException('Scheme source ' . $sourceFile . ' contains a non-string service at category ' . $categoryName . ', index ' . $serviceIndex . '.');
                    }

                    $name = trim($serviceName);
                    if ($name === '') {
                        throw new \RuntimeException('Scheme source ' . $sourceFile . ' contains an empty service name in category ' . $categoryName . '.');
                    }

                    // Excluded services are removed in the catalog loading path.
                    if (self::isExcludedService($name)) {
                        continue;
                    }

                    $schemes[] = [
                        'name' => $name,
                        'lsp' => null,
                        'units' => [],
                        'category' => $categoryName,
                    ];
                }
            }

            $decoded = [
                'title' => 'Layanan',
                'schemes' => $schemes,
            ];
        }

        // Backward-compatible support for a flat top-level list of service names.
        if (array_is_list($decoded) && isset($decoded[0]) && is_string($decoded[0])) {
            $serviceNames = array_values(array_filter(array_map('trim', $decoded), static fn (string $v): bool => $v !== ''));
            $decoded = [
                'title' => 'Layanan',
                'schemes' => array_map(
                    static fn (string $name): array => ['name' => $name, 'lsp' => null, 'units' => []],
                    $serviceNames
                ),
            ];
        }

        if (!isset($decoded['schemes']) || !is_array($decoded['schemes'])) {
            throw new \RuntimeException('Scheme source ' . $sourceFile . ' does not contain the expected "schemes" structure.');
        }

        $sourceTitle = trim((string) ($decoded['title'] ?? 'Layanan'));

        $adapted = [];
        foreach (array_values($decoded['schemes']) as $index => $scheme) {
            if (!is_array($scheme)) {
                throw new \RuntimeException('Scheme source ' . $sourceFile . ' contains a non-object scheme at index ' . $index . '.');
            }

            $categoryName = trim((string) ($scheme['category'] ?? $scheme['source_title'] ?? ''));
            if ($categoryName === '') {
                $categoryName = $sourceTitle !== '' ? $sourceTitle : 'Layanan';
            }
            $schemeSourceTitle = trim((string) ($scheme['source_title'] ?? $categoryName));

            $adapted[] = [
                'name' => $scheme['name'] ?? null,
                'lsp' => $scheme['lsp'] ?? null,
                'units' => array_values($scheme['units'] ?? []),
                // Source attribution is mandatory for unified records.
                'source' => $sourceKey,
                'source_key' => $sourceKey,
                'category' => $categoryName,
                'category_label' => $categoryName,
                'source_title' => $schemeSourceTitle,
                'source_file' => $sourceFile,
                'source_index' => $index,
                '_slug' => Str::slug((string) ($scheme['name'] ?? '')),
            ];
        }

        // Never expose excluded services through catalog metadata either.
        $adapted = array_values(array_filter(
            $adapted,
            static fn (array $s): bool => !self::isExcludedService((string) ($s['name'] ?? ''))
        ));

        $unitCount = array_sum(array_map(
            static fn (array $s): int => count($s['units']),
            $adapted
        ));

        $meta = [
            'key' => $sourceKey,
            'source' => $sourceKey,
            'source_key' => $sourceKey,
            'title' => $sourceTitle,
            'source_title' => $sourceTitle,
            'file' => $sourceFile,
            'source_file' => $sourceFile,
            'count' => count($adapted),
            'scheme_count' => count($adapted),
            'unit_count' => $unitCount,
            'image_url' => self::SOURCE_IMAGES[$sourceKey] ?? '/images/home/sumber-teknisi.jpg',
            'image_alt' => self::SOURCE_IMAGE_ALTS[$sourceKey] ?? ('Layanan ' . $categoryName),
        ];

        return [$meta, $adapted];
    }

    protected function filterLoaded(array $programs, array $filters): array
    {
        $result = [];

        foreach ($programs as $slug => $program) {
            if (!$this->matchesFilters($program, $filters)) {
                continue;
            }
            $result[$slug] = $program;
        }

        return $result;
    }

    protected function matchesFilters(array $program, array $filters): bool
    {
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (in_array($key, ['source', 'source_key', 'source_name', 'source_title_check'], true)) {
                $source = (string) ($program['source'] ?? $program['source_key'] ?? '');
                if ($source !== (string) $value) {
                    return false;
                }
                continue;
            }

            if ($key === 'category' || $key === 'source_category') {
                if ((string) ($program['source'] ?? '') !== (string) $value && (string) ($program['source_title'] ?? '') !== (string) $value) {
                    return false;
                }
                continue;
            }

            if ($key === 'q' || $key === 'query' || $key === 'search') {
                $haystack = implode(' ', [
                    (string) ($program['title'] ?? ''),
                    (string) ($program['source_title'] ?? ''),
                    (string) ($program['slug'] ?? ''),
                ]);
                if (stripos($haystack, (string) $value) === false) {
                    return false;
                }
                continue;
            }

            if ($key === 'min_units') {
                if ((int) count($program['units'] ?? []) < (int) $value) {
                    return false;
                }
                continue;
            }

            if ($key === 'max_units') {
                if ((int) count($program['units'] ?? []) > (int) $value) {
                    return false;
                }
                continue;
            }

            if ($key === 'slug' || $key === 'id') {
                if ((string) ($program['slug'] ?? '') !== (string) $value && (string) ($program['stable_id'] ?? '') !== (string) $value) {
                    return false;
                }
                continue;
            }

            if (array_key_exists($key, $program) && $program[$key] !== $value) {
                // Loose comparison for scalar metadata.
                if ((string) $program[$key] !== (string) $value) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function program(string $slug, string $name, array $scheme, array $sourceMeta): array
    {
        $units = array_values($scheme['units'] ?? []);
        $lsp = $scheme['lsp'] ?? null;
        $unitCount = count($units);
        $sourceKey = (string) ($scheme['source'] ?? $sourceMeta['key'] ?? '');
        $sourceTitle = (string) ($scheme['source_title'] ?? $sourceMeta['title'] ?? '');
        $sourceFile = (string) ($scheme['source_file'] ?? $sourceMeta['file'] ?? '');
        $categoryName = (string) ($scheme['category'] ?? ($sourceTitle !== '' ? $sourceTitle : 'Layanan'));
        $stableId = $sourceKey . ':' . $slug;

        return [
            'id' => $stableId,
            'stable_id' => $stableId,
            'source' => $sourceKey,
            'source_key' => $sourceKey,
            'source_title' => $sourceTitle,
            'source_name' => $sourceTitle,
            'source_file' => $sourceFile,
            'source_index' => $scheme['source_index'] ?? null,
            'slug' => $slug,
            'image_url' => self::SOURCE_IMAGES[$sourceKey] ?? '/images/home/sumber-teknisi.jpg',
            'image_alt' => self::SOURCE_IMAGE_ALTS[$sourceKey] ?? ('Layanan ' . $categoryName),
            'category' => 'training-service',
            'category_label' => $categoryName,
            'sector' => Str::slug($categoryName),
            'level' => $lsp ?? ('Layanan ' . $categoryName),
            'title' => $name,
            'badge' => $lsp ?? ('Layanan ' . $categoryName),
            'meta_title' => $name . ' | ' . config('app.name', 'Najkaltare'),
            'meta_description' => 'Layanan ' . $categoryName . ' ' . $name . '. Rincian program dikonfirmasi melalui konsultasi.',
            'hero_heading' => $categoryName,
            'hero_highlight' => $name,
            'hero_subheading' => 'Informasi layanan ' . $categoryName . ' ' . $name . '; rincian penyelenggaraan dikonfirmasi melalui konsultasi.',
            'wa_message' => 'Halo, saya ingin konsultasi mengenai ' . $name . '.',
            'introduction' => 'Layanan ' . $name . ' merupakan bagian dari ' . $categoryName . '. Rincian kurikulum dan persyaratan penyelenggaraan dikonfirmasi melalui konsultasi.',
            'target_audience' => [
                'Pihak yang membutuhkan layanan ' . $categoryName . '; kecocokan latar belakang dikonfirmasi melalui konsultasi.',
            ],
            'roles_and_responsibilities' => [
                [
                    'title' => 'Kompetensi Skema',
                    'description' => 'Rincian lingkup materi layanan ini dikonfirmasi melalui konsultasi.',
                ],
            ],
            'curriculum_topics' => array_map(
                static fn (array $unit): string => trim((string) ($unit['code'] ?? '')) . ' — ' . trim((string) ($unit['name'] ?? '')),
                $units
            ),
            'training_info' => [
                'jadwal' => ['label' => 'Jadwal Pelatihan', 'status' => 'Hubungi kami', 'detail' => 'Jadwal dikonfirmasi melalui konsultasi dengan penyelenggara.'],
                'durasi' => ['label' => 'Durasi Program', 'status' => 'Perlu verifikasi', 'detail' => 'Durasi dan moda penyelenggaraan dikonfirmasi bersama penyelenggara.'],
                'investasi' => ['label' => 'Investasi Biaya', 'status' => 'Hubungi kami', 'detail' => 'Biaya tidak dipublikasikan. Hubungi kami untuk penawaran resmi.'],
                'persyaratan' => ['label' => 'Persyaratan Peserta', 'status' => 'Perlu verifikasi', 'detail' => 'Persyaratan peserta dikonfirmasi ke penyelenggara skema.'],
                'otorisasi' => ['label' => 'Status Otorisasi', 'status' => 'Perlu verifikasi', 'detail' => 'Status skema kompetensi dikonfirmasi bersama LSP/penyelenggara terkait.'],
            ],
            'faqs' => [
                [
                    'question' => 'Berapa durasi pelaksanaan skema ini?',
                    'answer' => 'Durasi belum dipublikasikan dan perlu verifikasi melalui konsultasi dengan penyelenggara.',
                ],
                [
                    'question' => 'Bagaimana cara mendapatkan informasi biaya?',
                    'answer' => 'Biaya tidak dipublikasikan. Silakan hubungi kami melalui WhatsApp untuk informasi resmi.',
                ],
            ],
            'lsp' => $lsp,
            'units' => $units,
        ];
    }

    /**
     * Determine whether a catalog service name is excluded from selection/generation.
     */
    public static function isExcludedService(string $name): bool
    {
        $value = mb_strtolower(trim($name));
        if ($value === '') {
            return false;
        }

        $needles = [
            'juru ledak',
            'juru dinamit',
            'explosive',
            'explosives',
            'bahan peledak',
            'peledak',
            'dinamit',
            'ledak',
            'vaccine',
            'vaksin',
            'vaccination',
            'imunisasi',
            'bulk virus',
            'obat',
            'medicine',
            'medicin',
            'drug',
            'farmasi',
            'pharma',
            'pharmaceutical',
            'apotek',
            'penyakit',
            'disease',
            'illness',
            'sakit',
            'campak',
            'rubella',
            'polio',
            'influenza',
            'hepatitis',
            'covid',
            'kanker',
            'malaria',
            'tbc',
            'tuberkul',
            'diabetes',
            'asma',
            'tumor',
            'rabies',
            'demam',
            'infeksi',
            'virus',
        ];

        foreach ($needles as $needle) {
            if (str_contains($value, $needle)) {
                return true;
            }
        }

        return false;
    }

    public static function isExcluded(string $name): bool
    {
        return self::isExcludedService($name);
    }

    /**
     * Complete representative metadata for all 21 service categories.
     *
     * @return array{image: string, alt: string, description: string}
     */
    public static function categoryMetadata(string $categoryName): array
    {
        $slug = Str::slug($categoryName);

        $metadata = [
            'alat-berat-crane-rigging' => [
                'image' => '/images/categories/alat-berat-crane-rigging.jpg',
                'alt' => 'Operator alat berat dan mobile crane dalam kegiatan rigging industri',
                'description' => 'Pelatihan dan sertifikasi keahlian operator alat berat, rigger, mobile crane, dan operasional logistik alat industri.',
            ],
            'bisnis-manajemen-administrasi' => [
                'image' => '/images/categories/bisnis-manajemen-administrasi.jpg',
                'alt' => 'Pertemuan manajemen bisnis dan profesional di ruang kantor modern',
                'description' => 'Pengembangan kompetensi tata kelola bisnis, administrasi perkantoran, manajemen SDM, dan kepemimpinan korporasi.',
            ],
            'desain-kreatif-komunikasi' => [
                'image' => '/images/categories/desain-kreatif-komunikasi.jpg',
                'alt' => 'Desainer grafis profesional bekerja di studio kreatif modern',
                'description' => 'Standar keahlian desain komunikasi visual, multimedia terapan, periklanan, dan kreasi konten profesional.',
            ],
            'energi-kimia-proses' => [
                'image' => '/images/categories/energi-kimia-proses.jpg',
                'alt' => 'Fasilitas kilang industri pemrosesan kimia dan energi terbarukan',
                'description' => 'Kompetensi teknologi proses kimia, pengolahan energi fosil dan terbarukan, serta keandalan operasi pabrik.',
            ],
            'keamanan-pengamanan-tanggap-darurat' => [
                'image' => '/images/categories/keamanan-pengamanan-tanggap-darurat.jpg',
                'alt' => 'Personel pengamanan industri dan tanggap darurat keselamatan kerja',
                'description' => 'Kualifikasi satuan pengamanan, mitigasi bahaya industri, kesiapsiagaan darurat, dan proteksi aset fisik.',
            ],
            'kecantikan-spa-kebugaran' => [
                'image' => '/images/categories/kecantikan-spa-kebugaran.jpg',
                'alt' => 'Perawatan relaksasi spa dan kesehatan kebugaran profesional',
                'description' => 'Keahlian terapis estetika, perawatan spa aromaterapi, tata rias profesional, dan layanan kebugaran tubuh.',
            ],
            'keselamatan-kerja-k3' => [
                'image' => '/images/categories/keselamatan-kerja-k3.jpg',
                'alt' => 'Inspektur K3 dengan rompi keselamatan dan helm memeriksa area kerja',
                'description' => 'Sertifikasi Ahli K3 umum, analisis risiko tempat kerja, inspeksi kepatuhan regulasi, dan pencegahan kecelakaan.',
            ],
            'keuangan-perbankan-asuransi' => [
                'image' => '/images/categories/keuangan-perbankan-asuransi.jpg',
                'alt' => 'Analisis data finansial, perbankan investasi dan perencanaan risiko',
                'description' => 'Kompetensi akuntansi industri, analisis kredit perbankan, underwriting asuransi, dan manajemen kepatuhan keuangan.',
            ],
            'konstruksi-bangunan' => [
                'image' => '/images/categories/konstruksi-bangunan.jpg',
                'alt' => 'Proyek konstruksi bangunan bertingkat dan rekayasa teknik sipil',
                'description' => 'Kualifikasi pengawasan struktur bangunan, rekayasa sipil, manajemen proyek lapangan, dan keselamatan konstruksi.',
            ],
            'layanan-sosial-umum' => [
                'image' => '/images/categories/layanan-sosial-umum.jpg',
                'alt' => 'Pemberdayaan tenaga sosial dan pendampingan masyarakat publik',
                'description' => 'Peningkatan mutu fasilitator sosial, konseling pemberdayaan masyarakat, dan tata kelola layanan umum.',
            ],
            'lingkungan-limbah-pengolahan-air' => [
                'image' => '/images/categories/lingkungan-limbah-pengolahan-air.jpg',
                'alt' => 'Fasilitas pengolahan air bersih dan instalasi pengelolaan limbah ramah lingkungan',
                'description' => 'Pengelolaan limbah B3, pengendalian pencemaran air dan udara, audit AMDAL, dan keandalan sistem IPAL.',
            ],
            'listrik-elektronika-instrumentasi' => [
                'image' => '/images/categories/listrik-elektronika-instrumentasi.jpg',
                'alt' => 'Instalasi panel distribusi listrik, kontrol sakelar dan instrumentasi modern',
                'description' => 'Keahlian teknisi instalasi tenaga listrik, kalibrasi sistem kontrol, pemeliharaan panel, dan otomatisasi sirkuit.',
            ],
            'manufaktur-fabrikasi-metalurgi' => [
                'image' => '/images/categories/manufaktur-fabrikasi-metalurgi.jpg',
                'alt' => 'Pekerja pabrik manufaktur melakukan pengelasan baja dan fabrikasi logam',
                'description' => 'Kompetensi teknik pengelasan spesialis, pemesinan presisi (CNC), fabrikasi baja, dan pengujian material metalurgi.',
            ],
            'pariwisata-perhotelan-kuliner' => [
                'image' => '/images/categories/pariwisata-perhotelan-kuliner.jpg',
                'alt' => 'Layanan keramahan hotel berbintang dan hidangan kuliner profesional',
                'description' => 'Standar operasional front office perhotelan, manajemen tata boga (culinary art), dan tata kelola industri wisata.',
            ],
            'pendidikan-pelatihan-sertifikasi' => [
                'image' => '/images/categories/pendidikan-pelatihan-sertifikasi.jpg',
                'alt' => 'Instruktur menyampaikan materi workshop pelatihan vokasi interaktif',
                'description' => 'Kualifikasi instruktur pelatihan kerja, metodologi pembelajaran terapan, dan sertifikasi asesor kompetensi nasional.',
            ],
            'pertambangan-migas-geologi' => [
                'image' => '/images/categories/pertambangan-migas-geologi.jpg',
                'alt' => 'Operasional tambang terbuka, alat angkut tambang dan eksplorasi migas',
                'description' => 'Standar operasional tambang mineral batubara, keselamatan migas, pemetaan topografi, dan survei geologi eksplorasi.',
            ],
            'pertanian-perkebunan-kehutanan' => [
                'image' => '/images/categories/pertanian-perkebunan-kehutanan.jpg',
                'alt' => 'Area perkebunan produktif dan agribisnis berkelanjutan',
                'description' => 'Kompetensi budidaya komoditas perkebunan, mekanisasi pertanian modern, proteksi tanaman, dan kelestarian hutan.',
            ],
            'peternakan-perikanan-kelautan' => [
                'image' => '/images/categories/peternakan-perikanan-kelautan.jpg',
                'alt' => 'Aktivitas budidaya perikanan laut dan manajemen peternakan komersial',
                'description' => 'Keahlian teknologi budidaya perikanan tangkap, nautika kapal niaga, pengolahan hasil laut, dan nutrisi peternakan.',
            ],
            'teknis-profesi-lainnya' => [
                'image' => '/images/categories/teknis-profesi-lainnya.jpg',
                'alt' => 'Teknisi ahli industri melakukan perawatan mekanikal peralatan kerja',
                'description' => 'Ragam kompetensi mekanik industri, pemeliharaan utilitas bangunan, perbaikan presisi, dan vokasi teknis terapan.',
            ],
            'teknologi-informasi-digital' => [
                'image' => '/images/categories/teknologi-informasi-digital.jpg',
                'alt' => 'Pusat data server komputer jaringan dan infrastruktur komputasi modern',
                'description' => 'Keahlian rekayasa perangkat lunak, administrasi jaringan, keamanan siber (cybersecurity), dan arsitektur cloud.',
            ],
            'transportasi-logistik-pengemudi' => [
                'image' => '/images/categories/transportasi-logistik-pengemudi.jpg',
                'alt' => 'Pusat distribusi logistik pergudangan armada kargo terintegrasi',
                'description' => 'Standar manajemen pergudangan (warehousing), distribusi rantai pasok barang, pengemudi defensif, dan ekspedisi kargo.',
            ],
        ];

        return $metadata[$slug] ?? [
            'image' => '/images/home/hero-industrial.jpg',
            'alt' => 'Layanan dan pelatihan profesional industri ' . config('app.name', 'Najkaltare'),
            'description' => 'Katalog layanan dan pelatihan profesional terpadu dari ' . config('app.name', 'Najkaltare') . ' untuk mendukung peningkatan kompetensi industri.',
        ];
    }
}
