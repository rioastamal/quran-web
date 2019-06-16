<?php
/**
 * Super simple script to generate static HTML quran pages from
 * quran-json project. Each surah is having its own directory.
 * As an example Surah number #2 Al-Baqarah can be accessed using
 * following URL.
 *
 * https://hostname/2
 *
 * Full URL is https://hostname/2/index.html
 *
 * @author Rio Astamal <rio@rioastamal.net>
 */
class SurahGenerator
{
    /**
     * @var Array
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
     * @var Array
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

    const VERSION = '1.6';

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
            'langId' => 'id',
            'beginSurah' => 1,
            'endSurah' => 114,
            'appName' => 'QuranWeb',
            'analyticsId' => null
        ];
        $this->config = $config + $defaultConfig;

        $requiredConfig = ['quranJsonDir', 'buildDir', 'publicDir', 'templateDir', 'baseUrl'];
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
     * @return SurahGenerator
     */
    public function setConfig($key, $value)
    {
        $this->config[$key] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Generate each surah of the Quran.
     *
     * @return void
     * @throws exception
     */
    public function makeSurah()
    {
        $surahWithoutBasmalah = [1, 9];
        $indexTemplate = file_get_contents($this->config['templateDir'] . '/index-layout.html');
        $tafsirTemplate = file_get_contents($this->config['templateDir'] . '/tafsir-layout.html');
        $footerTemplate = $this->getFooterTemplate();
        $headerTemplate = str_replace('{{VERSION}}', static::VERSION, $this->getHeaderTemplate());
        $menuTemplate = $this->getMenuTemplate();

        foreach (range($this->config['beginSurah'], $this->config['endSurah']) as $surahNumber) {
            $jsonFile = $this->config['quranJsonDir'] . '/surah/' . $surahNumber . '.json';
            if (!file_exists($jsonFile)) {
                throw new RuntimeException('Can not find json file: ' . $jsonFile);
            }

            $surahJson = json_decode(file_get_contents($jsonFile), $asArray = true);
            if (!isset($surahJson[$surahNumber])) {
                throw new RuntimeException('Can not decode JSON file: ' . $jsonFile);
            }
            $surahJson = $surahJson[$surahNumber];
            $surahDir = $this->config['buildDir'] . '/public/' . $surahNumber;

            if (!file_exists($surahDir)) {
                mkdir($surahDir, 0755, $recursive = true);
            }

            $title = sprintf('Al-Quran Surah %s Terjemahan dan Tafsir Bahasa Indonesia', $surahJson['name_latin']);
            $keywords = 'al-quran, terjemahan, surah ' . $surahJson['name_latin'];
            $description = sprintf('Al-Quran Surah %s merupakan surah ke-%s yang terdiri dari %s ayat. Lengkap dengan terjemahan dan tafsir Bahasa Indonesia',
                    $surahJson['name_latin'], $surahJson['number'], $surahJson['number_of_ayah']);

            $metaHeader = $this->buildMetaTemplate([
                'keywords' => $keywords,
                'description' => $description
            ]);
            $metaHeader = array_merge($this->buildMetaTemplate([
                    'og:title' => $title,
                    'og:description' => $description,
                    'og:url' => $this->config['baseUrl'] . '/' . $surahJson['number'] . '/',
                    'og:image' => $this->config['ogImageUrl']
                ], 'property'),
                $metaHeader
            );

            $prevNextSurahUrl = $this->getPrevNextSurahUrl($surahNumber);

            $surahHeaderTemplate = str_replace('{{TITLE}}', $title, $headerTemplate);
            $surahHeaderTemplate = str_replace('{{META}}', implode("\n", $metaHeader), $surahHeaderTemplate);

            $surahTemplate = file_get_contents($this->config['templateDir'] . '/surah-layout.html');
            $surahTemplate = str_replace([
                    '{{SURAH_NUMBER}}',
                    '{{SURAH_NAME_LATIN}}',
                    '{{SURAH_NAME_ARABIC}}',
                    '{{TOTAL_AYAH}}',
                    '{{PREV_SURAH_NAME}}',
                    '{{PREV_URL}}',
                    '{{NEXT_SURAH_NAME}}',
                    '{{NEXT_URL}}',
                    '{{PAGE_NAME}}',
                    '{{HEADER}}',
                    '{{MENU}}',
                    '{{FOOTER}}'
                ],
                [
                    $surahJson['number'],
                    $surahJson['name_latin'],
                    $surahJson['name'],
                    $surahJson['number_of_ayah'],
                    $prevNextSurahUrl['prevSurahName'],
                    $prevNextSurahUrl['prevUrl'],
                    $prevNextSurahUrl['nextSurahName'],
                    $prevNextSurahUrl['nextUrl'],
                    'Surah',
                    $surahHeaderTemplate,
                    $menuTemplate,
                    $footerTemplate
                ],
                $surahTemplate
            );

            $ayahTemplate = '';
            $lang = $this->config['langId'];
            $tafsirSources = array_keys($surahJson['tafsir'][$lang]);

            if (!in_array($surahNumber, $surahWithoutBasmalah)) {
                $ayahTemplate = $this->getBasmalahTemplate();
            }

            for ($ayah = 1; $ayah <= $surahJson['number_of_ayah']; $ayah++) {
                // Concat each ayah text to be put on each surah file
                $ayahTemplate .= $this->getAyahTemplate([
                    'surah_number' => $surahNumber,
                    'surah_name' => $surahJson['name_latin'],
                    'ayah_text' => $surahJson['text'][$ayah],
                    'ayah_number' => $ayah,
                    'ayah_translation' => $surahJson['translations'][$lang]['text'][$ayah],
                    'tafsir_url' => $this->config['baseUrl'] . '/' . $surahNumber . '/' . $ayah . '/'
                ]);

                // Each ayah/tafsir having its own directory
                $ayahDir = $surahDir . '/' . $ayah;
                if (!file_exists($ayahDir)) {
                    mkdir($ayahDir, 0755, true);
                }

                $listOfTafsir = [];
                foreach ($tafsirSources as $tafsirSource) {
                    $listOfTafsir[] = [
                        'tafsir_name' => $surahJson['tafsir'][$lang][$tafsirSource]['name'],
                        'tafsir_source' => $surahJson['tafsir'][$lang][$tafsirSource]['source'],
                        'ayah_tafsir' => $surahJson['tafsir'][$lang][$tafsirSource]['text'][$ayah]
                    ];
                }
                $prevNext = $this->getPrevNextTafsirUrl($surahNumber, $ayah);

                $tafsirTextTemplate = $this->getTafsirTemplate([
                    'list_of_tafsir' => $listOfTafsir,
                ]);

                $tafsirFile = $ayahDir . '/index.html';
                $title = sprintf('Terjemahan dan Tafsir Quran surah %s ayat %s dalam Bahasa Indonesia', $surahJson['name_latin'], $ayah);
                $mergedTafsirName = implode(' dan ', array_column($listOfTafsir, 'tafsir_name'));
                $description = sprintf('Surah %s berarti %s. Sumber terjemahan dan tafsir %s ayat %s diambil dari %s.',
                        $surahJson['name_latin'],
                        $surahJson['translations'][$lang]['name'],
                        $surahJson['name_latin'],
                        $ayah,
                        $mergedTafsirName);
                $keywords = 'al-quran, baca quran, quran online, terjemahan, tafsir quran, surah ' . $surahJson['name_latin'] . ', ayat ' . $ayah;
                $metaHeader = $this->buildMetaTemplate([
                    'keywords' => $keywords,
                    'description' => $description
                ]);
                $metaHeader = array_merge($this->buildMetaTemplate([
                        'og:title' => $title,
                        'og:description' => $description,
                        'og:url' => $this->config['baseUrl'] . '/' . $surahJson['number'] . '/' . $ayah . '/',
                        'og:image' => $this->config['ogImageUrl']
                    ], 'property'),
                    $metaHeader
                );

                $tafsirHeaderTemplate = str_replace('{{TITLE}}', $title, $headerTemplate);
                $tafsirHeaderTemplate = str_replace('{{META}}', implode("\n", $metaHeader), $tafsirHeaderTemplate);
                $gotoTafsirAyahTemplate = $this->getGotoTafsirAyahTemplate($surahNumber, $surahJson['number_of_ayah']);

                $tafsirTextTemplate = str_replace([
                        '{{SURAH_NUMBER}}',
                        '{{SURAH_NAME_LATIN}}',
                        '{{SURAH_NAME_ARABIC}}',
                        '{{AYAH_NUMBER}}',
                        '{{SURAH_NUMBER}}',
                        '{{SURAH_URL}}',
                        '{{PREV_URL}}',
                        '{{PREV_SURAH_NAME}}',
                        '{{PREV_AYAH_NUMBER}}',
                        '{{NEXT_URL}}',
                        '{{NEXT_SURAH_NAME}}',
                        '{{NEXT_AYAH_NUMBER}}',
                        '{{EACH_GOTO_AYAH}}',
                        '{{AYAH_TEXT}}',
                        '{{AYAH_TRANSLATION}}',
                        '{{EACH_TAFSIR}}',
                        '{{PAGE_NAME}}',
                        '{{HEADER}}',
                        '{{MENU}}',
                        '{{FOOTER}}'
                    ],
                    [
                        $surahJson['number'],
                        $surahJson['name_latin'],
                        $surahJson['name'],
                        $ayah,
                        $surahNumber,
                        $this->config['baseUrl'] . '/' . $surahNumber . '/',
                        $prevNext['prevUrl'],
                        $prevNext['prevSurahName'],
                        $prevNext['prevAyah'],
                        $prevNext['nextUrl'],
                        $prevNext['nextSurahName'],
                        $prevNext['nextAyah'],
                        $gotoTafsirAyahTemplate,
                        $surahJson['text'][$ayah],
                        $surahJson['translations'][$lang]['text'][$ayah],
                        $tafsirTextTemplate,
                        'Tafsir Surah',
                        $tafsirHeaderTemplate,
                        $menuTemplate,
                        $footerTemplate
                    ],
                    $tafsirTemplate
                );

                file_put_contents($tafsirFile, $tafsirTextTemplate);
            }
            $surahTemplate = str_replace('{{EACH_AYAH}}', $ayahTemplate, $surahTemplate);
            $surahTemplate = str_replace(
                '{{EACH_GOTO_AYAH}}',
                $this->getGotoAyahTemplate($surahJson['number_of_ayah']),
                $surahTemplate);
            file_put_contents($surahDir . '/index.html', $surahTemplate);

            $indexSurahTemplate = $this->getSurahIndexTemplate([
                'base_url' => $this->config['baseUrl'],
                'surah_number' => $surahNumber,
                'surah_name' => $surahJson['name'],
                'surah_name_latin' => $surahJson['name_latin'],
                'number_of_ayah' => $surahJson['number_of_ayah']
            ]);
            $indexTemplate = str_replace('{{SURAH_INDEX}}', $indexSurahTemplate, $indexTemplate);
        }

        // Homepage
        $indexFile = $this->config['buildDir'] . '/public/index.html';
        $description = 'QuranWeb adalah Al-Quran online yang ringan dan cepat dengan terjemahan dan tafsir Bahasa Indonesia. Dapat diakses dari perangkat mobile dan komputer desktop.';
        $title = sprintf('Baca Al-Quran Online Terjemahan dan Tafsir Bahasa Indonesia', $surahJson['number']);

        $metaHeader = $this->buildMetaTemplate([
            'keywords' => 'al-quran, quran web, quran online, website quran, baca quran, quran digital',
            'description' => $description
        ]);
        $metaHeader = array_merge($this->buildMetaTemplate([
                'og:title' => $title,
                'og:description' => $description,
                'og:url' => $this->config['baseUrl'] . '/',
                'og:image' => $this->config['ogImageUrl']
            ], 'property'),
            $metaHeader
        );

        $indexHeaderTemplate = str_replace('{{TITLE}}', $title, $headerTemplate);
        $indexHeaderTemplate = str_replace('{{META}}', implode("\n", $metaHeader), $indexHeaderTemplate);
        $indexTemplate = str_replace([
            '{{PAGE_NAME}}',
            '{{HEADER}}',
            '{{FOOTER}}',
            '{{MENU}}'
        ],
        [
            'Baca Quran Online',
            $indexHeaderTemplate,
            $footerTemplate,
            $menuTemplate
        ], $indexTemplate);
        file_put_contents($indexFile, $indexTemplate);

        // About page
        $aboutTemplate = file_get_contents($this->config['templateDir'] . '/about-layout.html');
        if (!file_exists($this->config['buildDir'] . '/public/tentang')) {
            mkdir($this->config['buildDir'] . '/public/tentang', 0755, $recursive = true);
        }

        $title = 'Tentang ' . $this->config['appName'];
        $aboutFile = $this->config['buildDir'] . '/public/tentang/index.html';
        $description = 'QuranWeb adalah Al-Quran online dengan terjemahan Bahasa Indonesia. Dapat diakses dari perangkat mobile dan komputer desktop.';

        $metaHeader = $this->buildMetaTemplate([
            'keywords' => 'tentang al-quran, tentang quran web, tentang baca quran',
            'description' => $description
        ]);
        $metaHeader = array_merge($this->buildMetaTemplate([
                'og:title' => $title,
                'og:description' => $description,
                'og:url' => $this->config['baseUrl'] . '/tentang/',
                'og:image' => $this->config['ogImageUrl']
            ], 'property'),
            $metaHeader
        );

        $aboutHeaderTemplate = str_replace('{{TITLE}}', $title, $headerTemplate);
        $aboutHeaderTemplate = str_replace('{{META}}', implode("\n", $metaHeader), $aboutHeaderTemplate);

        $aboutTemplate = str_replace([
            '{{APP_NAME}}',
            '{{PAGE_NAME}}',
            '{{HEADER}}',
            '{{FOOTER}}',
            '{{MENU}}'
        ],
        [
            $this->config['appName'],
            'Info',
            $aboutHeaderTemplate,
            $footerTemplate,
            $menuTemplate
        ], $aboutTemplate);
        file_put_contents($aboutFile, $aboutTemplate);

        $robotsTxtFile = $this->config['buildDir'] . '/public/robots.txt';
        file_put_contents($robotsTxtFile, $this->getRobotsTxtContents());

        $sitemapFile = $this->config['buildDir'] . '/public/sitemap.xml';
        file_put_contents($sitemapFile, $this->getSitemapXmlContents());
    }

    /**
     * Basmalah template.
     *
     * @return string
     */
    public function getBasmalahTemplate()
    {
        return <<<BASMALAH

        <div class="ayah">
            <div class="ayah-text" dir="rtl"><p>بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ</p></div>
            <div class="ayah-translation"><p>Dengan nama Allah Yang Maha Pengasih, Maha Penyayang.</p></div>
        </div>

BASMALAH;
    }

    /**
     * Template of each ayah.
     *
     * @param array $params
     * @return string
     */
    public function getAyahTemplate(array $params)
    {
        $next = $this->getPrevNextTafsirUrl($params['surah_number'], $params['ayah_number']);

        return <<<AYAH

        <div class="ayah" id="no{$params['ayah_number']}" title="{$params['surah_name']},{$params['surah_number']},{$params['ayah_number']}">
            <div class="ayah-text" dir="rtl"><p>{$params['ayah_text']}<span class="ayah-number" dir="ltr">{$params['ayah_number']}</span></p></div>
            <div class="ayah-toolbar">
                <a class="icon-ayah-toolbar icon-back-to-top" title="Kembali ke atas" href="#"><span class="icon-content">&#x21e7;</span></a>
                <a class="icon-ayah-toolbar icon-mark-ayah link-mark-ayah" title="Tandai terakhir dibaca" href="#"><span class="icon-content">&#x2713;</span></a>
                <a class="icon-ayah-toolbar icon-tafsir-ayah" title="Tafsir Ayat" href="{$params['tafsir_url']}"><span class="icon-content">&#x273C;</span></a>
                <a class="icon-ayah-toolbar icon-play-audio murottal-audio-player" title="Audio Ayat"
                                            data-surah-number="{$params['surah_number']}"
                                            data-ayah-number="{$params['ayah_number']}"
                                            data-next-ayah-number="{$next['nextAyah']}"
                                            data-next-surah-number="{$next['nextSurahNumber']}"
                                            data-is-last-ayah="{$next['isLastAyah']}"
                                            data-from-tafsir-page="0"
                                            id="audio-{$params['surah_number']}-{$params['ayah_number']}"><span class="icon-content">&#x25b6;</span></a>
            </div>
            <div class="ayah-translation"><p>{$params['ayah_translation']}</p></div>
        </div>

AYAH;
    }

    /**
     * Template of each tafsir.
     *
     * @param array $params
     * @return string
     */
    public function getTafsirTemplate(array $params)
    {
        $allTafsir = '';
        foreach ($params['list_of_tafsir'] as $tafsir) {
            $text = str_replace("\n", '<br>', $tafsir['ayah_tafsir']);
            $allTafsir .= <<<ALL_TAFSIR

            <h3 class="ayah-tafsir-title">Tafsir {$tafsir['tafsir_name']}</h3>
            <div class="ayah-tafsir">{$text}</div>
            <div class="ayah-tafsir-source">
                Sumber:<br>{$tafsir['tafsir_source']}
            </div>

ALL_TAFSIR;
        }

        return $allTafsir;
    }

    /**
     * Template of homepage
     *
     * @param array $params
     * @return string
     */
    public function getSurahIndexTemplate(array $params)
    {
        $tag = '{{SURAH_INDEX}}';

        if ($params['surah_number'] == $this->config['endSurah']) {
            $tag = '';
        }

        $keywords = [
            $params['surah_name_latin'],
            // Remove single quote and replace "-" with space
            str_replace(["'", '-'], ['', ' '], $params['surah_name_latin']),
            // Remove single quote and replace "-" with empty string
            str_replace(["'", '-'], ['', ''], $params['surah_name_latin']),
        ];
        $keywords = implode(', ', $keywords);

        return <<<INDEX

                <li class="surah-index">
                    <a class="surah-index-link" href="{$params['base_url']}/{$params['surah_number']}/" title="Surah {$params['surah_name_latin']}" data-keywords="{$keywords}">
                        <span class="surah-index-name">{$params['surah_name_latin']} - {$params['surah_name']}</span>
                        <span class="surah-index-ayah">{$params['number_of_ayah']} Ayat</span>
                        <span class="surah-index-number">{$params['surah_number']}</span>
                    </a>
                </li>

                {$tag}
INDEX;
    }

    /**
     * @return string
     */
    public function getFooterTemplate()
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
    public function getHeaderTemplate()
    {
        $header = file_get_contents($this->config['templateDir'] . '/header-layout.html');
        $header = str_replace(['{{BASE_URL}}'], $this->config['baseUrl'], $header);

        return $header;
    }

    /**
     * @param string $headerTemplate
     * @param array $metas
     * @param string $attr
     * @return string
     */
    public function buildMetaTemplate(array $metas, $attr = 'name')
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

        if ($analyticsId = $this->config['analyticsId']) {
            $meta['gtag'] = <<<META
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$analyticsId}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '{$analyticsId}');
</script>
META;
        }

        return $meta;
    }

    /**
     * @return string
     */
    public function getMenuTemplate()
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
     * @return string
     */
    public function getGotoAyahTemplate($numberOfAyah)
    {
        $optionElement = '';

        for ($i=1; $i<=$numberOfAyah; $i++) {
            $optionElement .= sprintf('<option value="#no%d">%d</option>', $i, $i);
        }

        return $optionElement;
    }

    /**
     * @return string
     */
    public function getGotoTafsirAyahTemplate($surahNumber, $numberOfAyah)
    {
        $optionElement = '';

        for ($i=1; $i<=$numberOfAyah; $i++) {
            $optionElement .= sprintf('<option value="%s">%d</option>', $this->config['baseUrl'] . '/' . $surahNumber . '/' . $i . '/', $i);
        }

        return $optionElement;
    }

    /**
     * Copy public directory using shell. We did not want to implements
     * recursive copy.
     *
     * @return void
     */
    public function copyPublic()
    {
        static::recursiveCopy($this->config['publicDir'], $this->config['buildDir'] . '/public');
    }

    /**
     * Generate robots.txt contents
     *
     * @return string
     */
    public function getRobotsTxtContents()
    {
        return <<<ROBOTS
User-agent: *
Disallow:

sitemap: {$this->config['baseUrl']}/sitemap.xml
ROBOTS;
    }

    /**
     * Generate sitemap.xml contents
     *
     * @return string
     */
    public function getSitemapXmlContents()
    {
        $sitemap = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // All surah
        $lastMod = date('Y-m-d');
        for ($i=1; $i<=114; $i++) {
            $sitemap .= <<<SITEMAP
  <url>
    <loc>{$this->config['baseUrl']}/{$i}/</loc>
    <lastmod>{$lastMod}</lastmod>
    <changefreq>monthly</changefreq>
  </url>

SITEMAP;

            for ($ayah=1; $ayah<=$this->totalAyah[$i - 1]; $ayah++) {
            $sitemap .= <<<SITEMAP
  <url>
    <loc>{$this->config['baseUrl']}/{$i}/{$ayah}/</loc>
    <lastmod>{$lastMod}</lastmod>
    <changefreq>monthly</changefreq>
  </url>

SITEMAP;

            }
        }

        // Other pages
        $sitemap .= <<<SITEMAP
  <url>
    <loc>{$this->config['baseUrl']}/</loc>
    <lastmod>{$lastMod}</lastmod>
    <changefreq>monthly</changefreq>
    <priority>1</priority>
  </url>
  <url>
    <loc>{$this->config['baseUrl']}/tentang/</loc>
    <lastmod>{$lastMod}</lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.9</priority>
  </url>
</urlset>
SITEMAP;

        return $sitemap;
    }

    /**
     * Get prev and next Url for tafsir
     *
     * @param int $surahNumber
     * @param int $ayah
     * @return array
     */
    protected function getPrevNextTafsirUrl($surahNumber, $ayah)
    {
        // Prev and Next Url
        $prevAyah = $ayah - 1;
        $nextAyah = $ayah + 1;
        $nextSurahNumber = $surahNumber;
        $prevSurahNumber = $surahNumber;
        $isLastAyah = false;

        // Start from zero
        $prevSurahName = $this->surahNames[$surahNumber - 1];
        $nextSurahName = $this->surahNames[$surahNumber - 1];

        if ($surahNumber !== 1 && $ayah === 1) {
            // - 1 is current surah
            // - 2 is prev surah because started from 0
            $prevAyah = $this->totalAyah[$surahNumber - 2];
            $prevSurahName = $this->surahNames[$surahNumber - 2];
            $prevSurahNumber = $surahNumber - 1;
        }

        if ($surahNumber !== 114 && $ayah === $this->totalAyah[$surahNumber - 1]) {
            $nextSurahName = $this->surahNames[$surahNumber];
            $nextSurahNumber = $surahNumber + 1;
            $nextAyah = 1;
            $isLastAyah = true;
        }

        // Al-fatihah:1
        if ($surahNumber === 1 && $ayah === 1) {
            $prevSurahNumber = 114;

            // An-Nas:6
            $prevSurahName = $this->surahNames[113];
            $prevAyah = 6;
        }

        // An-Nas:6
        if ($surahNumber === 114 && $ayah === $this->totalAyah[113]) {
            $nextSurahName = $this->surahNames[0];
            $nextSurahNumber = 1;
            $nextAyah = 1;
            $isLastAyah = true;
        }

        return [
            'prevUrl' => $this->config['baseUrl'] . '/' . $prevSurahNumber . '/' . $prevAyah . '/',
            'nextUrl' => $this->config['baseUrl'] . '/' . $nextSurahNumber . '/' . $nextAyah . '/',
            'prevAyah' => $prevAyah,
            'nextAyah' => $nextAyah,
            'prevSurahName' => $prevSurahName,
            'nextSurahName' => $nextSurahName,
            'prevSurahNumber' => $prevSurahNumber,
            'nextSurahNumber' => $nextSurahNumber,
            'isLastAyah' => $isLastAyah
        ];
    }

    /**
     * Get prev and next Url for surah
     *
     * @param int $surahNumber
     * @return array
     */
    protected function getPrevNextSurahUrl($surahNumber)
    {
        $nextSurahNumber = $surahNumber + 1;
        $prevSurahNumber = $surahNumber - 1;

        // Al-fatihah
        if ($surahNumber === 1) {
            $prevSurahNumber = 114;
        }

        // An-Nas
        if ($surahNumber === 114) {
            $nextSurahNumber = 1;
        }

        // Index of $this->surahNames are start from zero
        $prevSurahName = $this->surahNames[$prevSurahNumber - 1];
        $nextSurahName = $this->surahNames[$nextSurahNumber - 1];

        return [
            'prevUrl' => $this->config['baseUrl'] . '/' . $prevSurahNumber . '/',
            'nextUrl' => $this->config['baseUrl'] . '/' . $nextSurahNumber . '/',
            'prevSurahName' => $prevSurahName,
            'nextSurahName' => $nextSurahName,
        ];
    }

    /**
     * Recursive copy directory
     *
     * @param string $dst
     * @param string $src
     * @return void
     * @credit http://php.net/manual/en/function.copy.php#91010
     */
    public static function recursiveCopy($src, $dst)
    {
        $dir = opendir($src);

        if (!file_exists($dst)) {
            mkdir($dst);
        }

        while (false !== ( $file = readdir($dir)) ) {
            if ($file != '.' && $file != '..' ) {

                if (is_dir($src . '/' . $file) ) {
                    static::recursiveCopy($src . '/' . $file, $dst . '/' . $file);
                    continue;
                }

                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
        closedir($dir);
    }
}
