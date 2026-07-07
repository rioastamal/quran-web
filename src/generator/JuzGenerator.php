<?php
/**
 * Generator for Juz (part) pages of the Quran based on the Indonesian Standard Mushaf.
 * Each of the 30 Juz gets its own directory: build/public/juz/{juz_number}/index.html
 * A Juz index page is also generated: build/public/juz/index.html
 *
 * @author Rio Astamal <rio@rioastamal.net>
 */
class JuzGenerator
{
    const VERSION = '1.10.1';

    /**
     * Juz boundaries based on the Indonesian Standard Mushaf.
     * Each entry: 'start' => [surahNumber, ayahNumber], 'end' => [surahNumber, ayahNumber]
     *
     * @var array
     */
    protected $juzMap = [
        1  => ['start' => [1,   1],  'end' => [2,  141]],
        2  => ['start' => [2,  142], 'end' => [2,  252]],
        3  => ['start' => [2,  253], 'end' => [3,   91]],
        4  => ['start' => [3,   92], 'end' => [4,   23]],
        5  => ['start' => [4,   24], 'end' => [4,  147]],
        6  => ['start' => [4,  148], 'end' => [5,   82]],
        7  => ['start' => [5,   83], 'end' => [6,  110]],
        8  => ['start' => [6,  111], 'end' => [7,   87]],
        9  => ['start' => [7,   88], 'end' => [8,   40]],
        10 => ['start' => [8,   41], 'end' => [9,   93]],
        11 => ['start' => [9,   94], 'end' => [11,   5]],
        12 => ['start' => [11,   6], 'end' => [12,  52]],
        13 => ['start' => [12,  53], 'end' => [15,   1]],
        14 => ['start' => [15,   2], 'end' => [16, 128]],
        15 => ['start' => [17,   1], 'end' => [18,  74]],
        16 => ['start' => [18,  75], 'end' => [20, 135]],
        17 => ['start' => [21,   1], 'end' => [22,  78]],
        18 => ['start' => [23,   1], 'end' => [25,  20]],
        19 => ['start' => [25,  21], 'end' => [27,  59]],
        20 => ['start' => [27,  60], 'end' => [29,  44]],
        21 => ['start' => [29,  45], 'end' => [33,  30]],
        22 => ['start' => [33,  31], 'end' => [36,  21]],
        23 => ['start' => [36,  22], 'end' => [39,  31]],
        24 => ['start' => [39,  32], 'end' => [41,  46]],
        25 => ['start' => [41,  47], 'end' => [45,  37]],
        26 => ['start' => [46,   1], 'end' => [51,  30]],
        27 => ['start' => [51,  31], 'end' => [57,  29]],
        28 => ['start' => [58,   1], 'end' => [66,  12]],
        29 => ['start' => [67,   1], 'end' => [77,  50]],
        30 => ['start' => [78,   1], 'end' => [114,  6]],
    ];

    /**
     * @var array
     */
    protected $surahNames = [
        "Al-Fatihah", "Al-Baqarah", "Ali 'Imran", "An-Nisa'",
        "Al-Ma'idah", "Al-An'am", "Al-A'raf", "Al-Anfal",
        "At-Taubah", "Yunus", "Hud", "Yusuf", "Ar-Ra'd",
        "Ibrahim", "Al-Hijr", "An-Nahl", "Al-Isra'", "Al-Kahf",
        "Maryam", "Taha", "Al-Anbiya'", "Al-Hajj", "Al-Mu'minun",
        "An-Nur", "Al-Furqan", "Asy-Syu'ara'", "An-Naml", "Al-Qasas",
        "Al-'Ankabut", "Ar-Rum", "Luqman", "As-Sajdah", "Al-Ahzab", "Saba'",
        "Fatir", "Yasin", "As-Saffat", "Sad", "Az-Zumar", "Gafir", "Fussilat",
        "Asy-Syura", "Az-Zukhruf", "Ad-Dukhan", "Al-Jasiyah", "Al-Ahqaf",
        "Muhammad", "Al-Fath", "Al-Hujurat", "Qaf", "Az-Zariyat", "At-Tur",
        "An-Najm", "Al-Qamar", "Ar-Rahman", "Al-Waqi'ah", "Al-Hadid",
        "Al-Mujadalah", "Al-Hasyr", "Al-Mumtahanah", "As-Saff", "Al-Jumu'ah",
        "Al-Munafiqun", "At-Tagabun", "At-Talaq", "At-Tahrim", "Al-Mulk",
        "Al-Qalam", "Al-Haqqah", "Al-Ma'arij", "Nuh", "Al-Jinn", "Al-Muzzammil",
        "Al-Muddassir", "Al-Qiyamah", "Al-Insan", "Al-Mursalat", "An-Naba'",
        "An-Nazi'at", "'Abasa", "At-Takwir", "Al-Infitar", "Al-Mutaffifin",
        "Al-Insyiqaq", "Al-Buruj", "At-Tariq", "Al-A'la", "Al-Gasyiyah",
        "Al-Fajr", "Al-Balad", "Asy-Syams", "Al-Lail", "Ad-Duha", "Asy-Syarh",
        "At-Tin", "Al-'Alaq", "Al-Qadr", "Al-Bayyinah", "Az-Zalzalah", "Al-'Adiyat",
        "Al-Qari'ah", "At-Takasur", "Al-'Asr", "Al-Humazah", "Al-Fil", "Quraisy",
        "Al-Ma'un", "Al-Kausar", "Al-Kafirun", "An-Nasr", "Al-Lahab", "Al-Ikhlas",
        "Al-Falaq", "An-Nas"
    ];

    /**
     * @var array
     */
    protected $totalAyah = [
        7, 286, 200, 176, 120, 165, 206, 75, 129, 109, 123, 111,
        43, 52, 99, 128, 111, 110, 98, 135, 112, 78, 118, 64, 77,
        227, 93, 88, 69, 60, 34, 30, 73, 54, 45, 83, 182, 88, 75,
        85, 54, 53, 89, 59, 37, 35, 38, 29, 18, 45, 60, 49, 62, 55,
        78, 96, 29, 22, 24, 13, 14, 11, 11, 18, 12, 12, 30, 52, 52,
        44, 28, 28, 20, 56, 40, 31, 50, 40, 46, 42, 29, 19, 36, 25,
        22, 17, 19, 26, 30, 20, 15, 21, 11, 8, 8, 19, 5, 8, 8, 11,
        11, 8, 3, 9, 5, 4, 7, 3, 6, 3, 5, 4, 5, 6
    ];

    /**
     * Surahs that do not have Basmalah at the start.
     *
     * @var array
     */
    protected $surahWithoutBasmalah = [1, 9];

    /**
     * @var array
     */
    protected $config = [];

    /**
     * Constructor
     *
     * @param array $config
     * @return void
     */
    public function __construct(array $config)
    {
        $defaultConfig = [
            'langId'     => 'id',
            'appName'    => 'QuranWeb',
            'rawHtmlMeta' => ''
        ];
        $this->config = $config + $defaultConfig;

        $requiredConfig = ['quranJsonDir', 'buildDir', 'publicDir', 'templateDir'];
        foreach ($requiredConfig as $required) {
            if (!isset($this->config[$required])) {
                throw new InvalidArgumentException('Missing config "' . $required . '"');
            }
        }

        if (!file_exists($this->config['quranJsonDir'])) {
            throw new InvalidArgumentException('Can not find quran-json directory: ' . $this->config['quranJsonDir']);
        }
    }

    /**
     * Generate all 30 Juz pages plus the Juz index page.
     *
     * @return void
     * @throws Exception
     */
    public function makeJuz()
    {
        $juzBaseDir  = $this->config['buildDir'] . '/public/juz';
        $footerTemplate = $this->getFooterTemplate();
        $headerTemplate = str_replace('{{VERSION}}', static::VERSION, $this->getHeaderTemplate());
        $menuTemplate   = $this->getMenuTemplate();
        $juzTemplate    = file_get_contents($this->config['templateDir'] . '/juz-layout.html');

        if (!file_exists($juzBaseDir)) {
            mkdir($juzBaseDir, 0755, $recursive = true);
        }

        $indexEntriesHtml = '';

        for ($juzNumber = 1; $juzNumber <= 30; $juzNumber++) {
            $juzDir = $juzBaseDir . '/' . $juzNumber;
            if (!file_exists($juzDir)) {
                mkdir($juzDir, 0755, true);
            }

            $surahGroups  = $this->getAyahsForJuz($juzNumber);
            $eachAyahHtml = $this->buildAyahsHtml($juzNumber, $surahGroups);
            $prevNext     = $this->getPrevNextJuzUrl($juzNumber);

            $title       = sprintf('Al-Quran Juz %d - Terjemahan Bahasa Indonesia', $juzNumber);
            $description = sprintf('Baca Al-Quran Juz %d lengkap dengan terjemahan Bahasa Indonesia. Mulai dari Surah %s ayat %d hingga Surah %s ayat %d.',
                $juzNumber,
                $this->surahNames[$this->juzMap[$juzNumber]['start'][0] - 1],
                $this->juzMap[$juzNumber]['start'][1],
                $this->surahNames[$this->juzMap[$juzNumber]['end'][0] - 1],
                $this->juzMap[$juzNumber]['end'][1]
            );
            $keywords = 'al-quran, juz ' . $juzNumber . ', quran juz ' . $juzNumber . ', terjemahan';

            $metaHeader = $this->buildMetaTemplate([
                'keywords'    => $keywords,
                'description' => $description
            ]);
            $metaHeader = array_merge($this->buildMetaTemplate([
                    'og:title'       => $title,
                    'og:description' => $description,
                    'og:url'         => $this->config['baseUrl'] . '/juz/' . $juzNumber . '/',
                    'og:image'       => $this->config['ogImageUrl']
                ], 'property'),
                $metaHeader
            );

            $juzHeaderTemplate = str_replace('{{TITLE}}', $title, $headerTemplate);
            $juzHeaderTemplate = str_replace('{{META}}', implode("\n", $metaHeader), $juzHeaderTemplate);

            $pageHtml = str_replace([
                    '{{JUZ_NUMBER}}',
                    '{{EACH_AYAH}}',
                    '{{PREV_JUZ_NUMBER}}',
                    '{{PREV_URL}}',
                    '{{NEXT_JUZ_NUMBER}}',
                    '{{NEXT_URL}}',
                    '{{PAGE_NAME}}',
                    '{{HEADER}}',
                    '{{MENU}}',
                    '{{FOOTER}}'
                ],
                [
                    $juzNumber,
                    $eachAyahHtml,
                    $prevNext['prevJuzNumber'],
                    $prevNext['prevUrl'],
                    $prevNext['nextJuzNumber'],
                    $prevNext['nextUrl'],
                    'Juz',
                    $juzHeaderTemplate,
                    $menuTemplate,
                    $footerTemplate
                ],
                $juzTemplate
            );

            file_put_contents($juzDir . '/index.html', $pageHtml);

            // Accumulate index entries
            $indexEntriesHtml .= $this->getJuzIndexEntryTemplate($juzNumber);
        }

        // Generate Juz index page
        $this->makeJuzIndex($indexEntriesHtml, $headerTemplate, $menuTemplate, $footerTemplate);
    }

    /**
     * Generate the Juz index page (build/public/juz/index.html).
     *
     * @param string $indexEntriesHtml
     * @param string $headerTemplate
     * @param string $menuTemplate
     * @param string $footerTemplate
     * @return void
     */
    protected function makeJuzIndex($indexEntriesHtml, $headerTemplate, $menuTemplate, $footerTemplate)
    {
        $indexTemplate = file_get_contents($this->config['templateDir'] . '/juz-index-layout.html');

        $title       = 'Daftar Juz Al-Quran - Terjemahan Bahasa Indonesia';
        $description = 'Daftar 30 Juz Al-Quran lengkap dengan terjemahan Bahasa Indonesia. Baca Al-Quran online per Juz.';
        $keywords    = 'al-quran, daftar juz, juz quran, 30 juz, quran online';

        $metaHeader = $this->buildMetaTemplate([
            'keywords'    => $keywords,
            'description' => $description
        ]);
        $metaHeader = array_merge($this->buildMetaTemplate([
                'og:title'       => $title,
                'og:description' => $description,
                'og:url'         => $this->config['baseUrl'] . '/juz/',
                'og:image'       => $this->config['ogImageUrl']
            ], 'property'),
            $metaHeader
        );

        $indexHeaderTemplate = str_replace('{{TITLE}}', $title, $headerTemplate);
        $indexHeaderTemplate = str_replace('{{META}}', implode("\n", $metaHeader), $indexHeaderTemplate);

        $pageHtml = str_replace([
                '{{JUZ_INDEX}}',
                '{{PAGE_NAME}}',
                '{{HEADER}}',
                '{{MENU}}',
                '{{FOOTER}}'
            ],
            [
                $indexEntriesHtml,
                'Daftar Juz',
                $indexHeaderTemplate,
                $menuTemplate,
                $footerTemplate
            ],
            $indexTemplate
        );

        $juzBaseDir = $this->config['buildDir'] . '/public/juz';
        file_put_contents($juzBaseDir . '/index.html', $pageHtml);
    }

    /**
     * Load ayahs from JSON files for a given Juz number.
     * Returns an array of surah groups, each group containing:
     *   'surah_number', 'surah_name', 'surah_name_arabic',
     *   'first_ayah', 'last_ayah', 'ayahs' (keyed by ayah number),
     *   'translations' (keyed by ayah number)
     *
     * @param int $juzNumber
     * @return array
     * @throws RuntimeException
     */
    protected function getAyahsForJuz(int $juzNumber): array
    {
        $startSurah = $this->juzMap[$juzNumber]['start'][0];
        $startAyah  = $this->juzMap[$juzNumber]['start'][1];
        $endSurah   = $this->juzMap[$juzNumber]['end'][0];
        $endAyah    = $this->juzMap[$juzNumber]['end'][1];
        $lang       = $this->config['langId'];

        $groups = [];

        for ($surahNumber = $startSurah; $surahNumber <= $endSurah; $surahNumber++) {
            $jsonFile = $this->config['quranJsonDir'] . '/surah/' . $surahNumber . '.json';
            if (!file_exists($jsonFile)) {
                throw new RuntimeException('Can not find json file: ' . $jsonFile);
            }

            $surahJson = json_decode(file_get_contents($jsonFile), $asArray = true);
            if (!isset($surahJson[$surahNumber])) {
                throw new RuntimeException('Can not decode JSON file: ' . $jsonFile);
            }
            $surahJson = $surahJson[$surahNumber];

            $firstAyah = ($surahNumber === $startSurah) ? $startAyah : 1;
            $lastAyah  = ($surahNumber === $endSurah)   ? $endAyah   : (int)$surahJson['number_of_ayah'];

            $ayahs        = [];
            $translations = [];

            for ($ayah = $firstAyah; $ayah <= $lastAyah; $ayah++) {
                $ayahs[$ayah]        = $surahJson['text'][$ayah];
                $translations[$ayah] = $surahJson['translations'][$lang]['text'][$ayah];
            }

            $groups[] = [
                'surah_number'       => $surahNumber,
                'surah_name'         => $surahJson['name_latin'],
                'surah_name_arabic'  => $surahJson['name'],
                'first_ayah'         => $firstAyah,
                'last_ayah'          => $lastAyah,
                'ayahs'              => $ayahs,
                'translations'       => $translations,
            ];
        }

        return $groups;
    }

    /**
     * Build the full {{EACH_AYAH}} HTML string for a Juz page.
     *
     * @param int   $juzNumber
     * @param array $surahGroups  Output of getAyahsForJuz()
     * @return string
     */
    protected function buildAyahsHtml(int $juzNumber, array $surahGroups): string
    {
        $html = '';

        foreach ($surahGroups as $group) {
            $surahNumber = $group['surah_number'];

            // Surah header divider
            $html .= $this->getSurahHeaderInJuzTemplate([
                'surah_number'      => $surahNumber,
                'surah_name'        => $group['surah_name'],
                'surah_name_arabic' => $group['surah_name_arabic'],
            ]);

            // Basmalah — only when the surah starts from ayah 1 and is not Surah 1 or 9
            if (!in_array($surahNumber, $this->surahWithoutBasmalah) && $group['first_ayah'] === 1) {
                $html .= $this->getBasmalahTemplate();
            }

            foreach ($group['ayahs'] as $ayahNumber => $ayahText) {
                $html .= $this->getJuzAyahTemplate([
                    'juz_number'      => $juzNumber,
                    'surah_number'    => $surahNumber,
                    'surah_name'      => $group['surah_name'],
                    'ayah_number'     => $ayahNumber,
                    'ayah_text'       => $ayahText,
                    'ayah_translation' => $group['translations'][$ayahNumber],
                    'tafsir_url'      => $this->config['baseUrl'] . '/' . $surahNumber . '/' . $ayahNumber . '/',
                ]);
            }
        }

        return $html;
    }

    /**
     * Surah header divider shown before the first ayah of each surah within a Juz.
     *
     * @param array $params
     * @return string
     */
    protected function getSurahHeaderInJuzTemplate(array $params): string
    {
        return <<<HTML

        <div class="surah-header-in-juz">
            <span class="surah-number-name">{$params['surah_number']}</span>{$params['surah_name']} - {$params['surah_name_arabic']}
        </div>

HTML;
    }

    /**
     * Basmalah template (identical to SurahGenerator).
     *
     * @return string
     */
    protected function getBasmalahTemplate(): string
    {
        return <<<BASMALAH

        <div class="ayah">
            <div class="ayah-text" dir="rtl"><p>بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ</p></div>
            <div class="ayah-translation"><p>Dengan nama Allah Yang Maha Pengasih, Maha Penyayang.</p></div>
        </div>

BASMALAH;
    }

    /**
     * Template for a single ayah on a Juz page.
     * Differs from SurahGenerator::getAyahTemplate() in:
     *   - Uses 'link-mark-ayah-juz' CSS class on the bookmark icon
     *   - The .ayah div title format: "juzNumber,surahName,surahNumber,ayahNumber"
     *   - Adds data-juz-number attribute on .ayah div
     *
     * @param array $params
     * @return string
     */
    protected function getJuzAyahTemplate(array $params): string
    {
        $surahNumber = $params['surah_number'];
        $ayahNumber  = $params['ayah_number'];
        $juzNumber   = $params['juz_number'];

        return <<<AYAH

        <div class="ayah" id="s{$surahNumber}-a{$ayahNumber}" title="{$params['juz_number']},{$params['surah_name']},{$surahNumber},{$ayahNumber}" data-juz-number="{$juzNumber}">
            <div class="ayah-text" dir="rtl"><p>{$params['ayah_text']}<span class="ayah-number" dir="ltr">{$ayahNumber}</span></p></div>
            <div class="ayah-toolbar">
                <a class="icon-ayah-toolbar icon-back-to-top" title="Kembali ke atas" href="#"><span class="icon-content">&#x21e7;</span></a>
                <a class="icon-ayah-toolbar icon-mark-ayah link-mark-ayah-juz" title="Tandai terakhir dibaca (Juz)" href="#"><span class="icon-content">&#x2713;</span></a>
                <a class="icon-ayah-toolbar icon-tafsir-ayah" title="Tafsir Ayat" href="{$params['tafsir_url']}"><span class="icon-content">&#x273C;</span></a>
                <a class="icon-ayah-toolbar icon-play-audio murottal-audio-player" title="Audio Ayat"
                                            data-surah-number="{$surahNumber}"
                                            data-ayah-number="{$ayahNumber}"
                                            data-next-ayah-number="0"
                                            data-next-surah-number="0"
                                            data-is-last-ayah="0"
                                            data-from-tafsir-page="0"
                                            id="audio-{$surahNumber}-{$ayahNumber}"><span class="icon-content">&#x25b6;</span></a>
            </div>
            <div class="ayah-translation"><p>{$params['ayah_translation']}</p></div>
        </div>

AYAH;
    }

    /**
     * One <li> entry for the Juz index page.
     *
     * @param int $juzNumber
     * @return string
     */
    protected function getJuzIndexEntryTemplate(int $juzNumber): string
    {
        $startSurahIndex = $this->juzMap[$juzNumber]['start'][0] - 1;
        $endSurahIndex   = $this->juzMap[$juzNumber]['end'][0] - 1;
        $startAyah       = $this->juzMap[$juzNumber]['start'][1];
        $endAyah         = $this->juzMap[$juzNumber]['end'][1];
        $startSurahName  = $this->surahNames[$startSurahIndex];
        $endSurahName    = $this->surahNames[$endSurahIndex];
        $baseUrl         = $this->config['baseUrl'];

        return <<<INDEX

                <li class="surah-index">
                    <a class="surah-index-link" href="{$baseUrl}/juz/{$juzNumber}/" title="Juz {$juzNumber}">
                        <span class="surah-index-name">Juz {$juzNumber}</span>
                        <span class="surah-index-ayah">{$startSurahName} {$startAyah} - {$endSurahName} {$endAyah}</span>
                        <span class="surah-index-number">{$juzNumber}</span>
                    </a>
                </li>

INDEX;
    }

    /**
     * Get previous and next Juz URLs. Juz 1 wraps to 30 and vice versa.
     *
     * @param int $juzNumber
     * @return array
     */
    protected function getPrevNextJuzUrl(int $juzNumber): array
    {
        $prevJuzNumber = ($juzNumber === 1)  ? 30 : $juzNumber - 1;
        $nextJuzNumber = ($juzNumber === 30) ? 1  : $juzNumber + 1;

        return [
            'prevUrl'       => $this->config['baseUrl'] . '/juz/' . $prevJuzNumber . '/',
            'nextUrl'       => $this->config['baseUrl'] . '/juz/' . $nextJuzNumber . '/',
            'prevJuzNumber' => $prevJuzNumber,
            'nextJuzNumber' => $nextJuzNumber,
        ];
    }

    /**
     * @return string
     */
    protected function getFooterTemplate(): string
    {
        $footer = file_get_contents($this->config['templateDir'] . '/footer-layout.html');
        $footer = str_replace([
            '{{APP_NAME}}',
            '{{VERSION}}',
            '{{BASE_URL}}',
            '{{BASE_MUROTTAL_URL}}'
        ],
        [
            $this->config['appName'],
            static::VERSION,
            $this->config['baseUrl'],
            $this->config['baseMurottalUrl']
        ], $footer);

        return $footer;
    }

    /**
     * @return string
     */
    protected function getHeaderTemplate(): string
    {
        $header = file_get_contents($this->config['templateDir'] . '/header-layout.html');
        $header = str_replace(['{{BASE_URL}}'], $this->config['baseUrl'], $header);

        return $header;
    }

    /**
     * @return string
     */
    protected function getMenuTemplate(): string
    {
        $menu = file_get_contents($this->config['templateDir'] . '/menu-layout.html');
        $menu = str_replace([
            '{{APP_NAME}}',
            '{{BASE_URL}}',
            '{{GITHUB_PROJECT_URL}}'
        ],
        [
            $this->config['appName'],
            $this->config['baseUrl'],
            $this->config['githubProjectUrl']
        ], $menu);

        return $menu;
    }

    /**
     * Build <meta> tag array.
     *
     * @param array  $metas
     * @param string $attr
     * @return array
     */
    protected function buildMetaTemplate(array $metas, $attr = 'name'): array
    {
        $metaTemplate = '<meta {{ATTR}}="{{META_NAME}}" content="{{META_CONTENT}}">';
        $meta = [];
        foreach ($metas as $name => $value) {
            $meta[$name] = str_replace([
                '{{ATTR}}', '{{META_NAME}}', '{{META_CONTENT}}'
            ],
            [
                $attr, $name, $value
            ], $metaTemplate);
        }

        if ($rawHtmlMeta = $this->config['rawHtmlMeta']) {
            $meta['raw'] = str_replace("\\n", "\n", $rawHtmlMeta);
        }

        return $meta;
    }
}
