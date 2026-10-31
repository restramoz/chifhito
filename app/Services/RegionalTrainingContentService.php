<?php

namespace App\Services;

/**
 * Builds editorial context from the Ayana title and its derived category.
 * The source does not provide descriptions or curricula, so these notes are
 * general topic explanations and never claim official program specifications.
 */
class RegionalTrainingContentService
{
    public function for(array $program, ?array $region = null): array
    {
        $title = (string) $program['title'];
        $category = (string) $program['category_label'];
        $area = $region['label'] ?? null;
        $subject = $this->subjectContext($program);
        $audience = $this->audienceContext($program);

        $faqs = [
            [
                'question' => "Apa ruang lingkup {$title}?",
                'answer' => "Judul katalog menempatkan topik ini pada bidang {$category}. Secara umum, istilahnya berkaitan dengan {$subject['scope']} Katalog tidak menyertakan silabus atau rincian praktik, sehingga ruang lingkup resmi perlu diminta kepada penyelenggara.",
            ],
            [
                'question' => 'Siapa yang mungkin mempertimbangkan topik ini?',
                'answer' => "Topik ini mungkin relevan bagi {$audience['roles']}. Ini gambaran umum berdasarkan judul, bukan syarat pendaftaran atau keputusan kelayakan peserta.",
            ],
            [
                'question' => 'Apakah pelatihan ini memberikan sertifikasi atau otorisasi?',
                'answer' => 'Status sertifikasi, lisensi, dan otorisasi tidak dicantumkan dalam sumber judul. Jangan menganggap program ini memberikan pengakuan resmi; mintalah konfirmasi tertulis dari penyelenggara atau lembaga berwenang.',
            ],
            [
                'question' => 'Bagaimana mengetahui durasi, jadwal, biaya, dan materinya?',
                'answer' => 'Informasi tersebut belum tersedia pada sumber katalog. Sampaikan kebutuhan peserta dan minta penawaran serta outline resmi untuk diverifikasi sebelum membuat keputusan.',
            ],
        ];

        if ($area !== null) {
            $faqs[] = [
                'question' => "Apakah pelatihan dapat diselenggarakan di {$area}?",
                'answer' => "Halaman ini memakai {$area} sebagai konteks administratif. Lokasi penyelenggaraan dan ketersediaan belum dipastikan; konfirmasikan kebutuhan lokasi kepada penyelenggara.",
            ];
        }

        return [
            'subject' => $subject,
            'audience' => $audience,
            'lede' => $area === null
                ? "“{$title}” tercantum dalam katalog Chifheto pada bidang {$category}. Informasi berikut menjelaskan konteks umum judul; rincian program perlu dikonfirmasi."
                : "“{$title}” tercantum dalam katalog Chifheto pada bidang {$category}. Halaman ini menghubungkan topik tersebut dengan kebutuhan di {$area}, tanpa mengasumsikan aktivitas industri lokal.",
            'overview' => "Judul sumber secara spesifik menyebut “{$title}”. Dalam konteks umum, topik ini berkaitan dengan {$subject['scope']} Hubungan tersebut membantu pembaca menilai kecocokan awal, tetapi bukan ringkasan silabus: daftar Ayana hanya memuat judul dan kategori, bukan metode, kedalaman, atau hasil belajar.",
            'purpose' => "Pengenalan topik “{$title}” dapat membantu tim merumuskan pertanyaan teknis dan membandingkan kebutuhan kerja mereka dengan bidang {$category}. Tujuan belajar dan batas pembahasan program resmi tetap perlu diperoleh dari penyelenggara.",
            'audience_note' => "Bidang kerja yang mungkin berkaitan adalah {$audience['roles']}. Relevansi ini bersifat umum dan tidak menetapkan persyaratan, pengalaman minimum, atau kelayakan peserta.",
            'related_sectors' => $subject['related_sectors'],
            'competency_areas' => [
                [
                    'title' => 'Konteks teknis pada judul',
                    'description' => "Judul “{$title}” mengarah pada {$subject['scope']} Ini penjelasan umum tentang istilah topik, bukan pernyataan bahwa seluruh aspek tersebut masuk ke program resmi.",
                ],
                [
                    'title' => 'Penerapan pada pekerjaan',
                    'description' => "Topik dapat menjadi bahan diskusi bagi {$audience['roles']}. Penerapan aktual bergantung pada peralatan, prosedur, ruang lingkup tugas, dan kondisi tempat kerja masing-masing.",
                ],
                [
                    'title' => 'Batas informasi yang tersedia',
                    'description' => 'Sumber tidak mencantumkan unit kompetensi, silabus, asesmen, atau keluaran kerja. Mintalah dokumen resmi sebelum menetapkan target kompetensi maupun kebutuhan kepatuhan.',
                ],
            ],
            'consultation_stages' => [
                ['title' => 'Jelaskan konteks kerja', 'detail' => "Sebutkan bidang tugas atau objek pekerjaan yang membuat tim mempertimbangkan “{$title}”."],
                ['title' => 'Petakan kebutuhan peserta', 'detail' => 'Bahas peran, pengalaman, dan kebutuhan kerja. Hal ini membantu konsultasi awal, bukan menggantikan persyaratan resmi.'],
                ['title' => 'Minta ruang lingkup resmi', 'detail' => 'Konfirmasikan outline, metode, persyaratan, dan dokumen penyelenggara sebelum menyebutnya sebagai materi atau kurikulum program.'],
            ],
            'compliance_note' => $this->complianceNote($program),
            'regional_relevance' => $area === null ? null : "Untuk kebutuhan dari {$area}, topik “{$title}” relevan apabila tugas tim bersinggungan dengan {$subject['regional_activity']}. Dataset BPS pada halaman ini menjelaskan susunan administratif wilayah, bukan keberadaan perusahaan, fasilitas, proyek, atau kegiatan industri tertentu. Cocokkan kebutuhan berdasarkan lokasi dan pekerjaan yang benar-benar dimaksud.",
            'regional_data_note' => $area === null ? null : 'Data wilayah yang dipakai di sini hanya memverifikasi nama dan hubungan administratif wilayah; data tersebut tidak mengonfirmasi aktivitas industri setempat.',
            'faqs' => $faqs,
            'image' => $this->categoryImage($category, (string) $program['title']),
        ];
    }

    private function subjectContext(array $program): array
    {
        $title = mb_strtolower((string) $program['title']);
        $category = (string) $program['category_label'];

        $scope = match (true) {
            str_contains($title, 'pelumas'), str_contains($title, 'lubricant'), str_contains($title, 'lubrikasi') => 'pemilihan, kondisi, atau analisis pelumas sebagaimana ditunjukkan judul; jenis sampel, parameter, dan metode pemeriksaan perlu diverifikasi pada outline resmi.',
            str_contains($title, 'alat berat') => 'peralatan bergerak dan pekerjaan operasional atau pemeliharaan alat berat yang disebut pada judul; jenis unit dan praktik yang dibahas belum dirinci.',
            str_contains($title, 'drilling'), str_contains($title, 'pemboran') => 'proses pemboran dan pekerjaan teknis yang disebut dalam judul; detail operasi yang dicakup tetap mengikuti silabus penyelenggara.',
            str_contains($title, 'instrument'), str_contains($title, 'instrumen') => 'penggunaan atau pengelolaan instrumentasi yang disebut dalam judul; jenis perangkat dan praktiknya belum dirinci oleh katalog.',
            str_contains($title, 'listrik'), str_contains($title, 'elektr'), str_contains($title, 'power') => 'pekerjaan kelistrikan atau sistem tenaga sesuai topik yang tertulis; cakupan instalasi dan praktik kerja perlu dikonfirmasi.',
            str_contains($title, 'geologi'), str_contains($title, 'eksplorasi') => 'pengamatan atau pekerjaan geologi dan eksplorasi sesuai topik yang tertulis; metode yang digunakan tidak dijelaskan di katalog.',
            str_contains($title, 'smelter'), str_contains($title, 'hidrometalurgi'), str_contains($title, 'benefisiasi') => 'pengolahan dan pemurnian mineral sebagaimana ditunjukkan oleh istilah pada judul; tahapan yang dicakup belum tersedia di katalog.',
            str_contains($title, 'lingkungan'), str_contains($title, 'limbah'), str_contains($title, 'waste') => 'pengelolaan lingkungan atau material sisa sesuai istilah pada judul; kewajiban dan metode tertentu tidak dinyatakan oleh sumber.',
            str_contains($title, 'safety'), str_contains($title, 'keselamatan'), str_contains($title, 'k3') => 'praktik keselamatan pada konteks pekerjaan yang disebut dalam judul; program ini tidak dengan sendirinya membuktikan sertifikasi atau pemenuhan regulasi.',
            str_contains($title, 'manajemen'), str_contains($title, 'management'), str_contains($title, 'bisnis') => 'pengelolaan organisasi atau proses bisnis yang disebut dalam judul; cakupan fungsi dan metode belum dijabarkan oleh katalog.',
            str_contains($title, 'data'), str_contains($title, 'digital'), str_contains($title, 'komputer') => 'pemanfaatan teknologi atau data sesuai istilah pada judul; perangkat, sistem, dan tingkat pembahasan perlu dikonfirmasi.',
            default => "kegiatan profesional pada bidang {$category} dengan fokus pada topik yang disebut secara langsung dalam judul",
        };

        $regionalActivity = match ($category) {
            'Pertambangan dan Geologi' => 'pekerjaan geologi, eksplorasi, pertambangan, atau pengolahan mineral yang sesuai dengan topik program',
            'Minyak, Gas, dan Pemboran' => 'pekerjaan yang terkait operasi migas, pemboran, fasilitas, atau peralatan sesuai topik program',
            'Teknik, Listrik, dan Instrumentasi' => 'tugas engineering, pemeliharaan, kelistrikan, atau instrumentasi yang sesuai dengan judul',
            'Keselamatan dan Lingkungan' => 'tanggung jawab keselamatan kerja atau pengelolaan lingkungan yang sesuai dengan topik',
            'Energi dan Pengolahan Proses' => 'operasi energi, produksi, atau pengolahan proses yang sesuai dengan judul',
            'Bisnis dan Manajemen' => 'fungsi bisnis atau manajemen yang terkait dengan tema program',
            'Teknologi dan Data' => 'pekerjaan yang menggunakan data atau teknologi sesuai dengan tema program',
            default => "pekerjaan teknis atau operasional yang sesuai dengan tema {$category}",
        };

        $relatedSectors = match ($category) {
            'Pertambangan dan Geologi' => 'geologi, eksplorasi, pertambangan, atau pengolahan mineral',
            'Minyak, Gas, dan Pemboran' => 'operasi minyak dan gas serta pekerjaan pemboran',
            'Teknik, Listrik, dan Instrumentasi' => 'engineering, kelistrikan, instrumentasi, atau pemeliharaan teknis',
            'Keselamatan dan Lingkungan' => 'keselamatan kerja dan pengelolaan lingkungan',
            'Energi dan Pengolahan Proses' => 'energi dan pengolahan proses',
            'Bisnis dan Manajemen' => 'bisnis dan manajemen',
            'Teknologi dan Data' => 'teknologi dan pengelolaan data',
            default => 'pekerjaan teknis dan operasional',
        };

        return compact('scope', 'regionalActivity', 'relatedSectors') + [
            'regional_activity' => $regionalActivity,
            'related_sectors' => $relatedSectors,
        ];
    }

    private function audienceContext(array $program): array
    {
        $title = mb_strtolower((string) $program['title']);
        $category = (string) $program['category_label'];

        $roles = match (true) {
            str_contains($title, 'analisis pelumas'), str_contains($title, 'lubricant') => 'teknisi pemeliharaan, analis kondisi peralatan, atau personel yang menangani pelumasan mesin',
            str_contains($title, 'alat berat') => 'operator, teknisi pemeliharaan, atau personel yang pekerjaannya berkaitan dengan alat berat',
            str_contains($title, 'operator') => 'operator dan personel teknis yang pekerjaannya berkaitan langsung dengan objek pada judul',
            str_contains($title, 'supervisor'), str_contains($title, 'pengawas') => 'pengawas atau supervisor yang menangani bidang yang disebut dalam judul',
            $category === 'Pertambangan dan Geologi' => 'personel geologi, eksplorasi, pertambangan, atau pengolahan mineral dengan tugas yang sesuai topik',
            $category === 'Minyak, Gas, dan Pemboran' => 'personel operasi, pemeliharaan, engineering, atau pemboran yang menangani lingkup terkait',
            $category === 'Teknik, Listrik, dan Instrumentasi' => 'teknisi, engineer, atau personel pemeliharaan yang tugasnya sesuai topik',
            $category === 'Keselamatan dan Lingkungan' => 'personel operasional, keselamatan, atau lingkungan dengan tanggung jawab yang sesuai topik',
            $category === 'Energi dan Pengolahan Proses' => 'personel operasi proses, produksi, atau energi yang menangani lingkup terkait',
            $category === 'Bisnis dan Manajemen' => 'staf atau pengelola fungsi bisnis yang berkaitan dengan tema pada judul',
            $category === 'Teknologi dan Data' => 'staf teknis atau pengguna sistem yang membutuhkan konteks pada topik tersebut',
            default => 'personel profesional yang pekerjaannya berkaitan dengan topik pada judul',
        };

        return compact('roles');
    }

    private function complianceNote(array $program): ?string
    {
        if (in_array($program['category_label'], ['Keselamatan dan Lingkungan', 'Teknik, Listrik, dan Instrumentasi'], true)) {
            return 'Topik keselamatan, lingkungan, atau teknis pada judul tidak otomatis membuktikan pemenuhan peraturan, lisensi, atau sertifikasi tertentu. Verifikasi ketentuan yang berlaku dan status resmi program kepada pihak berwenang atau penyelenggara.';
        }

        return null;
    }

    private function categoryImage(string $category, string $title): array
    {
        $lowerTitle = mb_strtolower($title);
        if (str_contains($lowerTitle, 'alat berat') || str_contains($lowerTitle, 'crane') || str_contains($lowerTitle, 'rigging')) {
            return ['path' => '/images/categories/alat-berat-crane-rigging.jpg', 'alt' => 'Ilustrasi bidang alat berat dan pemeliharaan industri'];
        }

        $map = [
            'Pertambangan dan Geologi' => ['/images/categories/pertambangan-migas-geologi.jpg', 'Ilustrasi bidang pertambangan dan geologi'],
            'Minyak, Gas, dan Pemboran' => ['/images/categories/pertambangan-migas-geologi.jpg', 'Ilustrasi bidang minyak, gas, dan pemboran'],
            'Teknik, Listrik, dan Instrumentasi' => ['/images/categories/listrik-elektronika-instrumentasi.jpg', 'Ilustrasi bidang teknik, listrik, dan instrumentasi'],
            'Keselamatan dan Lingkungan' => ['/images/categories/keselamatan-kerja-k3.jpg', 'Ilustrasi bidang keselamatan kerja'],
            'Energi dan Pengolahan Proses' => ['/images/categories/energi-kimia-proses.jpg', 'Ilustrasi bidang energi dan proses'],
            'Bisnis dan Manajemen' => ['/images/categories/bisnis-manajemen-administrasi.jpg', 'Ilustrasi bidang bisnis dan manajemen'],
            'Teknologi dan Data' => ['/images/categories/teknologi-informasi-digital.jpg', 'Ilustrasi bidang teknologi dan data'],
        ];

        [$path, $alt] = $map[$category] ?? ['/images/categories/teknis-profesi-lainnya.jpg', 'Ilustrasi pelatihan teknis profesional'];

        return ['path' => $path, 'alt' => $alt];
    }
}
