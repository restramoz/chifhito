<?php

namespace App\Services;

use App\Models\Article;
use App\Support\WhatsApp;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OllamaService
{
    protected ?string $apiKey;

    protected ?string $baseUrl;

    protected ?string $model;

    protected int $timeout;

    public function __construct(?SchemeService $schemes = null)
    {
        $this->apiKey = config('services.ollama.key');
        $this->baseUrl = config('services.ollama.base_url');
        $this->model = config('services.ollama.model');
        $this->timeout = (int) config('services.ollama.timeout', 60);
        $this->schemes = $schemes ?? app(SchemeService::class);
    }

    /**
     * Resolve chat endpoint URL safely.
     */
    public function getChatEndpoint(): string
    {
        $base = rtrim((string) $this->baseUrl, '/');

        if (str_ends_with($base, '/api/chat')) {
            return $base;
        }

        return "{$base}/api/chat";
    }

    /**
     * Select a relevant topic automatically based on region and existing articles.
     *
     * @return array{topic: string, focus: string}
     */
    public function pickTopicForRegion(string $cityName, ?string $citySlug = null): array
    {
        $existingTitles = [];
        if ($citySlug) {
            $existingTitles = Article::where('target_city_slug', $citySlug)
                ->pluck('title')
                ->toArray();
        }

        // Pick an unused real scheme from the authoritative catalog.
        foreach ($this->schemes->all() as $scheme) {
            $name = (string) ($scheme['title'] ?? '');
            if ($name === '') {
                continue;
            }

            $isUsed = false;
            foreach ($existingTitles as $title) {
                if (stripos((string) $title, $name) !== false) {
                    $isUsed = true;
                    break;
                }
            }

            if (! $isUsed) {
                $units = array_values($scheme['units'] ?? []);
                $unitLabels = array_slice(array_map(
                    static fn (array $u): string => trim((string) ($u['code'] ?? '')).' — '.trim((string) ($u['name'] ?? '')),
                    $units
                ), 0, 5);

                return [
                    'topic' => "Panduan {$name} untuk Proyek di {$cityName}",
                    'focus' => 'Unit kompetensi terkait: '.implode(', ', $unitLabels),
                ];
            }
        }

        $count = count($existingTitles) + 1;

        return [
            'topic' => "Strategi Pengembangan Kompetensi di {$cityName} (Bagian {$count})",
            'focus' => 'Pengembangan kompetensi layanan berbasis skema dari berbagai industri',
        ];
    }

    /**
     * Build the program-context line for the system prompt.
     */
    protected function serviceContextLine(?string $serviceName): string
    {
        $brandName = $this->brandName();

        if ($serviceName === null || $serviceName === '') {
            return "{$brandName} adalah perusahaan layanan industri lintas sektor. Cerminkan cakupan katalog yang meliputi SIA, SLO, SLF, Riksa Uji, pelatihan, sertifikasi, inspeksi, dan layanan industri lainnya; jangan membatasi perusahaan pada satu industri.";
        }

        return "Program spesifik: {$serviceName}. Sebutkan {$serviceName} secara alami sebagai salah satu layanan {$brandName}, lalu konsentrasikan isi artikel pada lingkup layanan tersebut.";
    }

    /**
     * Resolve word count from direct input (validated as integer 6000-9000).
     */
    public function resolveWordCount(int $wordCount): int
    {
        return $wordCount;
    }

    /**
     * Generate an article draft via Ollama.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function generateArticle(array $params): array
    {
        // 1. Verifikasi kelengkapan konfigurasi tanpa memanggil API
        if (empty($this->baseUrl)) {
            return [
                'success' => false,
                'error' => 'Konfigurasi OLLAMA_BASE_URL belum diatur di file .env.',
            ];
        }

        if (empty($this->model)) {
            return [
                'success' => false,
                'error' => 'Konfigurasi OLLAMA_MODEL belum diatur di file .env.',
            ];
        }

        $cityName = trim((string) ($params['city_name'] ?? 'Indonesia'));
        $brandName = $this->brandName();
        $districtName = ! empty($params['district_name']) ? trim((string) $params['district_name']) : null;
        $citySlug = $params['city_slug'] ?? null;
        $wordCount = $this->resolveWordCount($params['word_count'] ?? 7500);

        // Optional service-aware generation: validate against the catalog.
        $serviceSlug = isset($params['service_slug']) ? (string) $params['service_slug'] : null;
        $serviceName = null;
        if ($serviceSlug !== null && $serviceSlug !== '') {
            $scheme = $this->schemes->find($serviceSlug);
            $serviceName = is_array($scheme) && ! empty($scheme['title']) ? (string) $scheme['title'] : null;
        }

        // Auto topic selection if not manually provided
        if (empty($params['topic'])) {
            $selected = $this->pickTopicForRegion($cityName, $citySlug);
            $topic = $selected['topic'];
            $focus = $selected['focus'];
        } else {
            $topic = trim((string) $params['topic']);
            $focus = $params['focus'] ?? ('Kompetensi ' . ($serviceName ?: 'layanan industri lintas sektor') . ' serta layanan terkait ' . ($serviceName ?: $brandName));
        }

        $locationContext = $districtName ? "Kecamatan {$districtName}, {$cityName}" : $cityName;

        $systemPrompt = <<<PROMPT
Anda adalah konsultan dan instruktur kompetensi profesional Indonesia yang menulis untuk {$brandName}, perusahaan layanan industri lintas sektor.
Tugas Anda adalah menulis artikel edukatif dan otoritatif dalam Bahasa Indonesia formal mengenai layanan {$brandName} sesuai topik yang dipilih. Cakupan perusahaan meliputi layanan SIA, SLO, SLF, Riksa Uji, pelatihan, sertifikasi, inspeksi, dan jasa industri lintas sektor; jangan menggambarkan perusahaan seolah hanya bergerak di satu industri. Struktur artikel harus mencakup: Pembuka (intro), Silabus, Materi, Isi Konten, dan Penutup (conclusion).
Nama organisasi yang digunakan: {$brandName}. Gunakan nama ini secara konsisten bila nama organisasi diperlukan dan jangan menggantinya dengan nama organisasi lain.
PERTIMBANGAN PROGRAM: {$this->serviceContextLine($serviceName)}

ATURAN KUALITATAS & GAYA PENULISAN:
1. Nada bahasa: Profesional, lugas, mengalir alami, berbobot untuk pengambil keputusan dan praktisi dari layanan yang dibahas.
2. Dilarang menggunakan filler AI klise seperti "Di era modern saat ini", "Tak dapat dipungkiri", atau kalimat berulang.
3. Rujuk regulasi atau standar hanya jika Anda yakin terhadap sumber resminya. Jangan mengarang nomor regulasi, pasal, jadwal tetap, biaya fiktif, alamat kantor palsu, atau klaim akreditasi/otorisasi.
4. Jangan mengarang jadwal tetap, biaya fiktif, alamat kantor palsu, atau klaim akreditasi sepihak. Arahkan konsultasi resmi melalui WhatsApp.
5. Gunakan tag HTML semantik: <h2>, <h3>, <p>, <ul>, <ol>, <li>, <strong>, <blockquote>, <table><thead><tr><th>...</th></tr></thead><tbody><tr><td>...</td></tr></tbody></table>.
6. Panjang target: Sekitar {$wordCount} kata (antara 6.000 hingga 9.000 kata).
7. Buat image_brief terpisah yang siap digunakan oleh desainer di Google Flow untuk menghasilkan gambar fotografi realistis beresolusi tinggi TANPA teks di gambar.
8. Bila program pelatihan spesifik disebutkan, konsentrasikan materi pada lingkup program tersebut dan hindari menyerempet program pelatihan lain.

OUTPUT WAJIB:
Kembalikan HANYA dokumen JSON murni tanpa pembungkus markdown ```json ```.
Format skema JSON yang wajib dipatuhi:
{
  "title": "Judul Artikel Informatif dan Otoritatif",
  "slug": "slug-url-ramah-seo",
  "excerpt": "Ringkasan eksekutif 2-3 kalimat mengenai intisari artikel.",
  "content": "<p>Isi artikel lengkap dalam HTML semantik...</p>",
  "seo_title": "SEO Title Max 60 Karakter | {$brandName}",
  "meta_description": "Meta description persuasif max 155 karakter.",
  "suggested_faqs": [
    {"q": "Pertanyaan 1...", "a": "Jawaban ringkas 1..."},
    {"q": "Pertanyaan 2...", "a": "Jawaban ringkas 2..."}
  ],
  "image_brief": {
    "concept": "Konsep visual foto aktivitas layanan dan industri yang relevan dalam Bahasa Indonesia",
    "english_prompt": "Detailed descriptive photo prompt in English for Google Flow: realistic photography of professional training or service activity in an Indonesian industrial or workplace environment, natural daylight, no text overlays, 8k",
    "aspect_ratio": "16:9",
    "recommended_alt": "Alt text ramah SEO dalam Bahasa Indonesia"
  }
}
PROMPT;

        $serviceLine = $serviceName !== null ? "- Program Spesifik: {$serviceName}\n" : '';

        $userPrompt = <<<PROMPT
Silakan buat artikel layanan dan skema kompetensi {$brandName} dengan rincian berikut:
- Organisasi: {$brandName}
- Cakupan layanan: SIA, SLO, SLF, Riksa Uji, pelatihan, sertifikasi, inspeksi, serta layanan industri lintas sektor.
- Topik Artikel: {$topic}
- Fokus Materi: {$focus}
- Lokasi Target: {$locationContext}
{$serviceLine}- Target Jumlah Kata: ~{$wordCount} kata (antara 6.000 - 9.000 kata)

Keluarkan HANYA JSON valid.
PROMPT;

        try {
            $endpoint = $this->getChatEndpoint();
            $client = Http::timeout($this->timeout);

            if (! empty($this->apiKey)) {
                $client = $client->withToken($this->apiKey);
            }

            $response = $client->post($endpoint, [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'stream' => false,
            ]);

            if (! $response->successful()) {
                Log::error('Ollama API error response', [
                    'status' => $response->status(),
                    'endpoint' => $endpoint,
                    'model' => $this->model,
                ]);

                return [
                    'success' => false,
                    'error' => "Ollama merespons dengan status HTTP {$response->status()}: ".Str::limit($response->body(), 200),
                ];
            }

            $data = $response->json();
            $rawContent = $data['message']['content'] ?? '';

            if (empty(trim($rawContent))) {
                return [
                    'success' => false,
                    'error' => 'Ollama mengembalikan respons konten kosong.',
                ];
            }

            $result = $this->parseResponse($rawContent, $topic, $cityName, $wordCount, $serviceName);

            // ---------------------------------------------------------------------
            // Enforce word-count constraints before adding UI elements. Callouts
            // must not influence the word-count validation.
            // ---------------------------------------------------------------------
            $generatedWordCount = $this->countWords($result['content'] ?? '');
            if ($generatedWordCount > 9000) {
                return [
                    'success' => false,
                    'error' => 'Artikel yang dihasilkan melebihi batas maksimum 9.000 kata.',
                ];
            }

            // Robust continuation loop: bounded rounds to reach the minimum word count.
            $maxAttempts = 8;
            $attempt = 0;
            while ($generatedWordCount < 6000 && $attempt < $maxAttempts) {
                $remaining = max(6000 - $generatedWordCount, 300);
                $continuation = $this->requestContinuation($result['content'], $remaining, $cityName, $serviceName);
                if (! $continuation['success']) {
                    return $continuation;
                }
                $result['content'] .= $continuation['content'];
                $generatedWordCount = $this->countWords($result['content']);
                $attempt++;
            }

            if ($generatedWordCount < 6000) {
                return [
                    'success' => false,
                    'error' => 'Artikel yang dihasilkan kurang dari batas minimum 6.000 kata.',
                ];
            }

            if ($generatedWordCount > 9000) {
                return [
                    'success' => false,
                    'error' => 'Artikel yang dihasilkan melebihi batas maksimum 9.000 kata.',
                ];
            }

            // After validation, inject five WhatsApp callouts.
            $result = $this->addWhatsAppCallout($result, $cityName, $serviceName);

            // Deterministically append the four article consultation links.
            $result = $this->addArticleConsultationBlock($result);

            return $result;
        } catch (ConnectionException $e) {
            Log::error('Ollama connection timeout or unreachable: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'Koneksi ke Ollama gagal atau timeout setelah '.$this->timeout.' detik. Pastikan layanan Ollama aktif dan endpoint dapat dijangkau.',
            ];
        } catch (\Throwable $e) {
            Log::error('OllamaService Exception: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'Terjadi kesalahan saat memproses permintaan artikel: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Robust JSON parsing and fallback structure.
     *
     * @return array<string, mixed>
     */
    public function parseResponse(string $rawContent, string $fallbackTopic, string $cityName, int $wordCount, ?string $serviceName = null): array
    {
        $brandLine = $this->brandLine($serviceName);
        $cleaned = trim($rawContent);

        // Strip markdown code fences if present
        if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/s', $cleaned, $matches)) {
            $cleaned = trim($matches[1]);
        } elseif (str_starts_with($cleaned, '```json')) {
            $cleaned = trim(substr($cleaned, 7));
            if (str_ends_with($cleaned, '```')) {
                $cleaned = trim(substr($cleaned, 0, -3));
            }
        } elseif (str_starts_with($cleaned, '```')) {
            $cleaned = trim(substr($cleaned, 3));
            if (str_ends_with($cleaned, '```')) {
                $cleaned = trim(substr($cleaned, 0, -3));
            }
        }

        // Remove stray markdown fences anywhere in the payload (mixed tags/text).
        $cleaned = preg_replace('/```(?:json)?/i', '', $cleaned) ?? $cleaned;
        $cleaned = str_replace('```', '', $cleaned);
        $cleaned = trim($cleaned);
        $cleaned = str_ireplace('Jucerdyamg', $this->brandName(), $cleaned);

        // Repair invalid JSON escape sequences produced by LaTeX/backslashes.
        $cleaned = $this->repairJsonEscapes($cleaned);

        $decoded = json_decode($cleaned, true);

        // If direct parse failed, extract the first balanced JSON object.
        if (! is_array($decoded)) {
            $json = $this->extractJsonObject($cleaned);
            if ($json !== null) {
                $decoded = json_decode($json, true);
            }
        }

        // Retry after removing trailing commas (common LLM mistake).
        if (! is_array($decoded)) {
            $repaired = preg_replace('/,\s*([}\]])/', '$1', $cleaned) ?? $cleaned;
            $decoded = json_decode($repaired, true);
            if (! is_array($decoded)) {
                $json = $this->extractJsonObject($repaired);
                if ($json !== null) {
                    $decoded = json_decode($json, true);
                }
            }
        }

        if (is_array($decoded) && ! empty($decoded['title'])) {
            $imageBrief = $decoded['image_brief'] ?? [];
            if (! is_array($imageBrief)) {
                $imageBrief = [];
            }

            $concept = $imageBrief['concept'] ?? "Penerapan layanan dan kompetensi di {$cityName}";
            $englishPrompt = $imageBrief['english_prompt'] ?? 'Professional documentary photograph of a service and competency training environment in Indonesia, realistic natural lighting, no text';
            $aspectRatio = $imageBrief['aspect_ratio'] ?? '16:9';
            $recommendedAlt = $imageBrief['recommended_alt'] ?? "Layanan {$brandLine} di {$cityName}";

            $rawArticleContent = $this->sanitizeContent((string) ($decoded['content'] ?? ''));

            return [
                'success' => true,
                'title' => (string) $decoded['title'],
                'slug' => ! empty($decoded['slug']) ? Str::slug($decoded['slug']) : Str::slug($decoded['title']),
                'excerpt' => (string) ($decoded['excerpt'] ?? Str::limit(strip_tags($rawArticleContent), 180)),
                'content' => $rawArticleContent,
                'seo_title' => (string) ($decoded['seo_title'] ?? Str::limit($decoded['title']." | {$brandLine}", 60)),
                'meta_description' => (string) ($decoded['meta_description'] ?? Str::limit($decoded['excerpt'] ?? '', 155)),
                'suggested_faqs' => is_array($decoded['suggested_faqs'] ?? null) ? $decoded['suggested_faqs'] : [],
                'image_brief' => [
                    'concept' => $concept,
                    'english_prompt' => $englishPrompt,
                    'aspect_ratio' => $aspectRatio,
                    'recommended_alt' => $recommendedAlt,
                ],
            ];
        }

        // Recover HTML content when the model returns imperfect JSON.
        $fallbackContent = $cleaned;

        if (preg_match('/"content"\s*:\s*("(?:\\\\.|[^"\\\\])*")/s', $cleaned, $match)) {
            $extracted = json_decode($match[1], true);
            if (is_string($extracted) && trim($extracted) !== '') {
                $fallbackContent = $extracted;
            }
        }

        if (preg_match('/<\s*(p|h[1-6]|table|thead|tbody|tr|td|th|ul|ol|li|blockquote)\b/i', $fallbackContent)) {
            $fallbackHtml = $this->sanitizeContent($fallbackContent);
        } else {
            $fallbackHtml = '<p>'.nl2br(e($fallbackContent)).'</p>';
        }

        return [
            'success' => true,
            'title' => $fallbackTopic,
            'slug' => Str::slug($fallbackTopic),
            'excerpt' => Str::limit(strip_tags($fallbackHtml), 180),
            'content' => $fallbackHtml,
            'seo_title' => Str::limit("{$fallbackTopic} | {$brandLine}", 60),
            'meta_description' => Str::limit("Panduan {$fallbackTopic} untuk layanan dan kompetensi di {$cityName}.", 155),
            'suggested_faqs' => [],
            'image_brief' => [
                'concept' => "Layanan di {$cityName}",
                'english_prompt' => 'Professional photograph of a electrical safety facility inspection or briefing inside a modern industrial facility in Indonesia, realistic lighting, no text',
                'aspect_ratio' => '16:9',
                'recommended_alt' => "{$brandLine} - {$fallbackTopic}",
            ],
        ];
    }

    /**
     * Resolved display line used in article fallbacks: the specific program
     * title when available, otherwise the general brand.
     */
    /**
     * Deterministically append the four article WhatsApp consultation numbers.
     *
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
  
    /**
     * Append exactly four clickable WhatsApp phone numbers to generated articles.
     *
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    
/**
 * Append exactly four clickable WhatsApp phone numbers to generated articles.
 *
 * @param array<string, mixed> $result
 * @return array<string, mixed>
 */
protected function addArticleConsultationBlock(array $result): array
{
    $content = (string) ($result['content'] ?? '');
    $consultationNumbers = WhatsApp::consultationNumbers();

    // Check whether all four numbers already exist as direct clickable links.
    $allLinksExist = true;

    foreach ($consultationNumbers as $number) {
        $url = WhatsApp::consultationUrl($number);

        if ($url === null) {
            $allLinksExist = false;
            break;
        }

        $pattern = '~<a\b[^>]*href=["\']'
            . preg_quote($url, '~')
            . '["\'][^>]*>\s*'
            . preg_quote($number, '~')
            . '\s*</a>~i';

        if (! preg_match($pattern, $content)) {
            $allLinksExist = false;
            break;
        }
    }

    // Preserve an already complete block without introducing duplicates.
    if (
        $allLinksExist
        && str_contains($content, 'article-consultation-numbers')
    ) {
        $result['content'] = $content;

        return $result;
    }

    // Remove old or incomplete consultation markup before rebuilding it.
    $content = $this->removeStaleConsultationMarkup($content);

    $links = [];

    foreach ($consultationNumbers as $number) {
        $url = WhatsApp::consultationUrl($number);

        if ($url === null) {
            continue;
        }

        $links[] = '<a href="' . e($url) . '" target="_blank" '
            . 'rel="noopener noreferrer" '
            . 'class="inline-flex items-center justify-center px-3 py-2 '
            . 'rounded-lg bg-white border border-neutral-200 '
            . 'text-sm font-bold text-dark-mahogany hover:text-burnt-brick '
            . 'transition-colors min-h-[44px] tracking-wide">'
            . e($number)
            . '</a>';
    }

    if ($links !== []) {
        $block = '<div class="article-consultation-numbers my-6 p-4 sm:p-6 '
            . 'rounded-2xl bg-neutral-50 border border-neutral-200 not-prose">'
            . '<h3 class="text-xs font-bold text-dark-mahogany uppercase '
            . 'tracking-wider">Konsultasi via WhatsApp</h3>'
            . '<div class="mt-3 grid grid-cols-1 sm:grid-cols-2 '
            . 'md:grid-cols-4 gap-2.5 sm:gap-3">'
            . implode('', $links)
            . '</div></div>';

        $content = rtrim($content) . "\n" . $block;
    }

    $result['content'] = $content;

    return $result;
}


        
           

    /**
     * Remove stale/partial consultation markup so it cannot duplicate or linger.
     */
    protected function removeStaleConsultationMarkup(string $content): string
    {
        // 1. Remove the deprecated generic "Konsultasikan Hasil Generate" button.
        $content = preg_replace(
            '/<(?:button|a)\b[^>]*>.*?Konsultasikan Hasil Generate.*?<\/(?:button|a)>/is',
            '',
            $content
        ) ?? $content;

        // 2. Remove any existing article-consultation-numbers wrapper blocks,
        //    including partial ones with nested <div> elements.
        $content = $this->removeNestedDivBlocks($content, 'article-consultation-numbers');

        return $content;
    }

    /**
     * Remove every <div ...marker...>...</div> block, correctly handling
     * nested <div> elements.
     */
    protected function removeNestedDivBlocks(string $content, string $marker): string
    {
        $markerLen = strlen($marker);
        while (true) {
            $markerPos = stripos($content, $marker);
            if ($markerPos === false) {
                break;
            }

            // Locate the opening <div of the wrapper.
            $openStart = strrpos(substr($content, 0, $markerPos), '<div');
            if ($openStart === false) {
                // Could not find a wrapper div; strip the marker and continue.
                $content = substr_replace($content, '', $markerPos, $markerLen);

                continue;
            }

            // Ensure the found marker sits inside the opening tag (before its '>').
            $tagEnd = strpos($content, '>', $openStart);
            if ($tagEnd === false || $tagEnd < $markerPos) {
                $content = substr_replace($content, '', $markerPos, $markerLen);

                continue;
            }

            // Walk forward, tracking div depth, to find the matching </div>.
            $depth = 0;
            $i = $openStart;
            $len = strlen($content);
            $closeEnd = null;
            while ($i < $len) {
                if (strtolower(substr($content, $i, 4)) === '<div' && ($i + 4 >= $len || ! ctype_alpha($content[$i + 4]))) {
                    $depth++;
                    $i += 4;

                    continue;
                }
                if (strtolower(substr($content, $i, 6)) === '</div>') {
                    $depth--;
                    $i += 6;
                    if ($depth === 0) {
                        $closeEnd = $i;
                        break;
                    }

                    continue;
                }
                $i++;
            }

            if ($closeEnd === null) {
                // Unbalanced; strip the whole remainder to be safe? Just strip marker.
                $content = substr_replace($content, '', $markerPos, $markerLen);
                break;
            }

            $content = substr($content, 0, $openStart).substr($content, $closeEnd);
        }

        return $content;
    }

    protected function brandLine(?string $serviceName): string
    {
        $brandName = $this->brandName();

        return ($serviceName === null || $serviceName === '')
            ? $brandName
            : $brandName . ' — ' . $serviceName;
    }

    protected function brandName(): string
    {
        return trim((string) config('app.name')) ?: 'Najkaltare';
    }

    /**
     * Add context-aware consultation callouts using article-specific numbers.
     *
     * Uses the fixed consultation numbers from WhatsApp::consultationNumbers().
     * Each of the five callouts rotates through the four available numbers.
     * All links are clickable with valid wa.me URLs, target="_blank", and
     * rel="noopener noreferrer".
     *
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    protected function addWhatsAppCallout(array $result, string $cityName, ?string $serviceName = null): array
    {
        $content = $this->sanitizeContent($result['content'] ?? '');
        $brandLine = $this->brandLine($serviceName);

        // Use article-specific consultation numbers (fixed set, never from global config).
        $consultationNumbers = WhatsApp::consultationNumbers();

        // Split content by closing paragraph tags to insert between sections.
        $paragraphEnds = [];
        $offset = 0;
        while (($pos = strpos($content, '</p>', $offset)) !== false) {
            $paragraphEnds[] = $pos + 4; // position after </p>
            $offset = $pos + 4;
        }

        // Determine insertion indices – distribute roughly evenly.
        $insertPoints = [];
        $countEnds = count($paragraphEnds);
        for ($i = 0; $i < 5; $i++) {
            if ($countEnds === 0) {
                // No paragraphs – append at the end.
                $insertPoints[] = null;
                continue;
            }
            // Calculate target index proportionally.
            $target = (int) round(($i + 1) * $countEnds / 6);
            $insertPoints[] = $paragraphEnds[$target] ?? end($paragraphEnds);
        }

        // Insert callouts in reverse order so offsets remain valid.
        foreach (array_reverse($insertPoints, true) as $index => $position) {
            // Rotate through the four consultation numbers.
            $number = $consultationNumbers[$index % count($consultationNumbers)];
            $url = WhatsApp::consultationUrl($number);

            // Should always be valid since consultationNumbers are validated constants.
            if ($url === null) {
                continue;
            }

            $message = "Halo, saya ingin konsultasi mengenai {$brandLine} untuk wilayah {$cityName}.";
            $waUrl = WhatsApp::url($message, $number);

            $calloutAction = '<a href="'.$waUrl.'" target="_blank" rel="noopener noreferrer"'
                .' class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs transition shrink-0">'
                .'<span>Konsultasi WhatsApp</span> &rarr;'
                .'</a>';

            $calloutHtml = '<div class="my-6 p-4 rounded-xl bg-concrete-100 border border-concrete-300 flex flex-col sm:flex-row items-center justify-between gap-4 not-prose">'
                .'<div>'
                .'<div class="text-xs font-bold uppercase tracking-wider text-teal-700">Konsultasi '.$brandLine.' '.$cityName.'</div>'
                .'<div class="text-sm font-semibold text-navy-950">Butuh informasi layanan atau skema kompetensi?</div>'
                .'</div>'
                .$calloutAction
                .'</div>';

            if ($position !== null) {
                $content = substr_replace($content, $calloutHtml, $position, 0);
            } else {
                $content .= $calloutHtml;
            }
        }

        $result['content'] = $content;

        return $result;
    }

    /**
     * Request a continuation from Ollama for the given partial content.
     * Returns an array with keys: success (bool) and content (string) on success.
     */
    protected function requestContinuation(string $existingContent, int $targetWords, string $cityName, ?string $serviceName = null): array
    {
        // Build a simple continuation prompt.
        $brandLine = $this->brandLine($serviceName);
        $systemPrompt = "Anda adalah editor artikel {$brandLine} berbahasa Indonesia. Lanjutkan artikel yang diberikan dengan konten baru sekitar {$targetWords} kata. Jangan mengulang paragraf atau judul yang sudah ada. Output HANYA fragmen HTML valid menggunakan tag semantik seperti <h2>, <h3>, <p>, <ul>, <ol>, <li>, <strong>, dan <table> bila relevan. Jangan keluarkan JSON, markdown fence, komentar, atau metadata. Jika tidak dapat memenuhi seluruh target, tetap keluarkan fragmen HTML valid yang bermakna.";
        $userPrompt = "Konten artikel yang sudah ada (gunakan sebagai konteks, jangan salin ulang):\n{$existingContent}\n\nTambahkan bagian lanjutan yang relevan dan substantif sebanyak kira-kira {$targetWords} kata. Fokus pada informasi baru, contoh penerapan lapangan, prosedur, risiko, pengendalian, dan kepatuhan regulasi yang relevan. Kembalikan hanya fragmen HTML tambahan, bukan artikel lengkap.";

        try {
            $endpoint = $this->getChatEndpoint();
            $client = Http::timeout($this->timeout);
            if (! empty($this->apiKey)) {
                $client = $client->withToken($this->apiKey);
            }
            $response = $client->post($endpoint, [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'stream' => false,
            ]);

            if (! $response->successful()) {
                return ['success' => false, 'error' => 'Continuation request failed with HTTP '.$response->status()];
            }

            $data = $response->json();
            $raw = $data['message']['content'] ?? '';
            if (trim($raw) === '') {
                return ['success' => false, 'error' => 'Empty continuation response'];
            }

            $parsed = $this->parseResponse($raw, $cityName, $cityName, $targetWords);
            if (empty($parsed['content'])) {
                return ['success' => false, 'error' => 'Continuation parsing produced no content'];
            }
            $continuationContent = trim($parsed['content']);
            $addedWords = $this->countWords($continuationContent);

            if ($addedWords === 0) {
                return ['success' => false, 'error' => 'Continuation contains no usable words'];
            }

            // Reject an identical response to avoid repeatedly appending the same content.
            $normalizedExisting = preg_replace('/\s+/u', ' ', strip_tags($existingContent));
            $normalizedContinuation = preg_replace('/\s+/u', ' ', strip_tags($continuationContent));

            if (
                is_string($normalizedExisting)
                && is_string($normalizedContinuation)
                && trim($normalizedContinuation) !== ''
                && str_contains($normalizedExisting, trim($normalizedContinuation))
            ) {
                return ['success' => false, 'error' => 'Continuation repeated existing content'];
            }

            // A shorter valid fragment is acceptable; generateArticle() can request another round.
            return ['success' => true, 'content' => $continuationContent];
        } catch (ConnectionException $e) {
            return ['success' => false, 'error' => 'Continuation connection error: '.$e->getMessage()];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => 'Continuation exception: '.$e->getMessage()];
        }
    }

    /**
     * Count words in a string while ignoring HTML tags and decoding entities.
     *
     * @param  string  $content  HTML content
     * @return int Number of words
     */
    public function countWords(string $content): int
    {
        // Remove HTML tags
        $text = strip_tags($content);
        // Decode HTML entities (e.g. &amp; -> &)
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Normalize whitespace and split on word boundaries
        $words = preg_split('/\s+/u', trim($text));

        return $words === false ? 0 : count(array_filter($words, fn ($w) => $w !== ''));
    }

    /**
     * Repair invalid JSON escape sequences (e.g. LaTeX backslashes) so that
     * json_decode can parse the payload.
     */
    protected function repairJsonEscapes(string $content): string
    {
        // Pass 1: backslash + 2 or more letters (LaTeX commands) -> escape backslash.
        $content = preg_replace_callback(
            '~\\\\[A-Za-z]{2,}~',
            static fn ($m) => '\\\\'.substr($m[0], 1),
            $content
        ) ?? $content;

        // Pass 2: backslash + single char; keep valid JSON escapes, escape the rest.
        $content = preg_replace_callback(
            '~\\\\(?:u[0-9a-fA-F]{4}|.)~s',
            static function ($m) {
                $esc = $m[0];
                if ($esc === '\\"' || $esc === '\\\\' || $esc === '\\/' ||
                    $esc === '\\b' || $esc === '\\f' || $esc === '\\n' ||
                    $esc === '\\r' || $esc === '\\t') {
                    return $esc;
                }
                if (preg_match('~^\\\\u[0-9a-fA-F]{4}$~', $esc)) {
                    return $esc;
                }
                if (strpos($esc, '\\u') === 0) {
                    return '\\\\'.substr($esc, 1);
                }

                return '\\\\'.substr($esc, 1);
            },
            $content
        ) ?? $content;

        return $content;
    }

    /**
     * Extract the first balanced JSON object from a string that may contain
     * surrounding prose or mixed tags.
     */
    protected function extractJsonObject(string $content): ?string
    {
        $start = strpos($content, '{');
        if ($start === false) {
            return null;
        }

        $depth = 0;
        $inString = false;
        $escape = false;
        $len = strlen($content);
        for ($i = $start; $i < $len; $i++) {
            $ch = $content[$i];
            if ($inString) {
                if ($escape) {
                    $escape = false;
                } elseif ($ch === '\\') {
                    $escape = true;
                } elseif ($ch === '"') {
                    $inString = false;
                }
            } else {
                if ($ch === '"') {
                    $inString = true;
                } elseif ($ch === '{') {
                    $depth++;
                } elseif ($ch === '}') {
                    $depth--;
                    if ($depth === 0) {
                        return substr($content, $start, $i - $start + 1);
                    }
                }
            }
        }

        return null;
    }

    protected function sanitizeContent(string $content): string
    {
        // ---------------------------------------------------------------
        // Phase 1: Strip block-level LaTeX math environments
        // e.g. \[ ... \], $$...$$, \begin{equation}...\end{equation}
        // ---------------------------------------------------------------
        $content = preg_replace('/\\\\\[[\s\S]*?\\\\\]/u', '', $content) ?? $content;
        $content = preg_replace('/\$\$[\s\S]*?\$\$/u', '', $content) ?? $content;
        $content = preg_replace('/\\\\begin\{[^}]+\}[\s\S]*?\\\\end\{[^}]+\}/u', '', $content) ?? $content;

        // ---------------------------------------------------------------
        // Phase 2: Convert inline LaTeX math $...$ to readable plain text
        // ---------------------------------------------------------------
        $content = preg_replace_callback('/\$([^$\n]+?)\$/u', function ($m) {
            $inner = $m[1];
            $inner = preg_replace('/\\\\text\{([^}]*)\}/u', '$1', $inner) ?? $inner;
            $inner = preg_replace('/\\\\mathrm\{([^}]*)\}/u', '$1', $inner) ?? $inner;
            $inner = preg_replace('/\\\\mathbf\{([^}]*)\}/u', '$1', $inner) ?? $inner;
            $inner = preg_replace('/\\\\frac\{([^}]*)\}\{([^}]*)\}/u', '$1/$2', $inner) ?? $inner;
            $inner = str_replace(
                ['\\rightarrow', '\\leftarrow', '\\Rightarrow', '\\Leftarrow', '\\leftrightarrow',
                    '\\times', '\\cdot', '\\pm', '\\leq', '\\geq', '\\neq', '\\approx',
                    '\\sum', '\\prod', '\\int', '\\infty',
                    '\\alpha', '\\beta', '\\gamma', '\\delta', '\\Delta', '\\sigma',
                    '\\mu', '\\lambda', '\\pi', '\\theta', '\\phi', '\\Sigma',
                    '_', '^', '\\', '{', '}'],
                ['→', '←', '⇒', '⇐', '↔',
                    '×', '·', '±', '≤', '≥', '≠', '≈',
                    '∑', '∏', '∫', '∞',
                    'α', 'β', 'γ', 'δ', 'Δ', 'σ',
                    'μ', 'λ', 'π', 'θ', 'φ', 'Σ',
                    '', '', '', '', ''],
                $inner
            );
            $inner = trim($inner);

            return $inner !== '' ? $inner : '';
        }, $content) ?? $content;

        // ---------------------------------------------------------------
        // Phase 3: Strip LaTeX commands with braces — extract inner text
        // \text{...}, \textbf{...}, \emph{...} etc.
        // ---------------------------------------------------------------
        $content = preg_replace_callback(
            '/\\\\(?:text|textbf|textit|emph|mathbf|mathrm|mathit|mathbb|mathcal|mathsf|operatorname)\{([^}]*)\}/u',
            fn ($m) => $m[1],
            $content
        ) ?? $content;

        // \frac{a}{b} \u2192 a/b
        $content = preg_replace_callback(
            '/\\\\frac\{([^}]*)\}\{([^}]*)\}/u',
            fn ($m) => $m[1].'/'.$m[2],
            $content
        ) ?? $content;

        // Remaining \command{...} wrappers \u2014 extract inner text
        $content = preg_replace('/\\\\[a-zA-Z]+\{([^}]*)\}/u', '$1', $content) ?? $content;

        // ---------------------------------------------------------------
        // Phase 4: Replace common standalone LaTeX symbols
        // ---------------------------------------------------------------
        $content = str_replace(
            ['\\rightarrow', '\\leftarrow', '\\Rightarrow', '\\Leftarrow', '\\leftrightarrow',
                '\\times', '\\cdot', '\\leq', '\\geq', '\\neq', '\\approx',
                '\\infty', '\\pm', '\\alpha', '\\beta', '\\gamma', '\\delta',
                '\\Delta', '\\sigma', '\\mu', '\\lambda', '\\pi', '\\theta', '\\phi', '\\Sigma'],
            ['→', '←', '⇒', '⇐', '↔',
                '×', '·', '≤', '≥', '≠', '≈',
                '∞', '±', 'α', 'β', 'γ', 'δ',
                'Δ', 'σ', 'μ', 'λ', 'π', 'θ', 'φ', 'Σ'],
            $content
        );

        // ---------------------------------------------------------------
        // Phase 5: Remove any remaining bare LaTeX backslash commands
        // ---------------------------------------------------------------
        $content = preg_replace('/\\\\[a-zA-Z]+\b/u', '', $content) ?? $content;

        // ---------------------------------------------------------------
        // Phase 6: Remove stray dollar signs and leftover curly braces
        // ---------------------------------------------------------------
        $content = str_replace(['$', '{', '}'], '', $content);

        // ---------------------------------------------------------------
        // Phase 7: Normalize excess whitespace (preserve HTML tags)
        // ---------------------------------------------------------------
        $content = preg_replace('/[ \t]{2,}/', ' ', $content) ?? $content;

        // Remove format-artifact square brackets (e.g. "[]" leftovers).
        $content = preg_replace('/\[\s*\]/u', '', $content) ?? $content;

        return $content;
    }
}
