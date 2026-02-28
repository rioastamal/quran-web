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

    const VERSION = '1.8';

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
     * Generate each surah of the Quran with support for multiple translations.
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
                throw new RuntimeException('Cannot find JSON file: ' . $jsonFile);
            }

            $surahJson = json_decode(file_get_contents($jsonFile), true);
            if (!isset($surahJson[$surahNumber])) {
                throw new RuntimeException('Cannot decode JSON file: ' . $jsonFile);
            }
            $surahJson = $surahJson[$surahNumber];
            $surahDir = $this->config['buildDir'] . '/public/' . $surahNumber;

            if (!file_exists($surahDir)) {
                mkdir($surahDir, 0755, true);
            }

            $ayahTemplate = '';
            $selectedLanguages = $this->config['selectedLanguages'] ?? ['en']; // Default to English if not set
            $includeTransliteration = $this->config['includeTransliteration'] ?? false;

            // Load translations for selected languages
            $translationsByLanguage = [];
            foreach ($selectedLanguages as $lang) {
                $translationsByLanguage[$lang] = $this->loadTranslationsFromXml($lang);
            }

            if (!in_array($surahNumber, $surahWithoutBasmalah)) {
                $ayahTemplate = $this->getBasmalahTemplate();
            }

            for ($ayah = 1; $ayah <= $surahJson['number_of_ayah']; $ayah++) {
                $translations = [];
                foreach ($selectedLanguages as $lang) {
                    $translations[$lang] = $translationsByLanguage[$lang][$surahNumber][$ayah] ?? '';
                }

                $ayahTextWithTajweed = $this->highlightTajweed($surahJson['text'][$ayah]);

                $transliteration = $includeTransliteration && isset($surahJson['transliteration']['text'][$ayah])
                    ? $surahJson['transliteration']['text'][$ayah]
                    : '';

                $ayahTemplate .= $this->getAyahTemplate([
                    'surah_number' => $surahNumber,
                    'surah_name' => $surahJson['name_latin'],
                    'ayah_text' => $ayahTextWithTajweed, // Apply Tajweed highlighting
                    'ayah_number' => $ayah,
                    'translations' => $translations,
                    'transliteration' => $transliteration,
                ]);
            }

            $surahFile = $surahDir . '/index.html';
            file_put_contents($surahFile, $ayahTemplate);
        }
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

        <div class="ayah" id="no{$params['ayah_number']}" title="{$params['surah_name']},{$params['surah_number']},{$params['ayah_number']}" data-is-last-ayah="{$next['isLastAyah']}" data-next-ayah-number="{$next['nextAyah']}">
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

        if ($rawHtmlMeta = $this->config['rawHtmlMeta']) {
            $meta['raw'] = str_replace("\\n", "\n", $rawHtmlMeta);
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

    /**
     * Load translations from XML files.
     *
     * @param string $languageCode
     * @return array
     */
    protected function loadTranslationsFromXml($languageCode)
    {
        $filePath = $this->config['dataDir'] . '/' . $languageCode . '.xml';
        if (!file_exists($filePath)) {
            throw new RuntimeException('Translation file not found: ' . $filePath);
        }

        $translations = [];
        $xml = simplexml_load_file($filePath);

        foreach ($xml->sura as $sura) {
            $suraIndex = (int) $sura['index'];
            foreach ($sura->aya as $aya) {
                $ayaIndex = (int) $aya['index'];
                $translations[$suraIndex][$ayaIndex] = (string) $aya['text'];
            }
        }

        return $translations;
    }

    /**
     * Tajweed rules with regex patterns and colors.
     *
     * @var array
     */
    protected $tajweedRules = [
        'Ikhfa’ Haqiqi' => ['pattern' => '/[صذثكجشقسدتزفطضظ]/u', 'color' => '#FFD700'], // Gold
        'Ikhfa’ Shafawi' => ['pattern' => '/[ف]/u', 'color' => '#FF69B4'], // Pink
        'Idgham Bighunnah' => ['pattern' => '/[ينمو]/u', 'color' => '#32CD32'], // LimeGreen
        'Idgham Bilaghunnah' => ['pattern' => '/[لر]/u', 'color' => '#1E90FF'], // DodgerBlue
        'Idgham Mimi' => ['pattern' => '/[م]/u', 'color' => '#FF8C00'], // DarkOrange
        'Idgham Shafawi' => ['pattern' => '/[ف]/u', 'color' => '#FF69B4'], // Pink
        'Iqlab' => ['pattern' => '/ب/u', 'color' => '#8A2BE2'], // BlueViolet
        'Qalqalah' => ['pattern' => '/[قجط]/u', 'color' => '#DC143C'], // Crimson
        'Ghunna' => ['pattern' => '/[م]/u', 'color' => '#20B2AA'], // LightSeaGreen
        'Mad Alif Lam Syamiah' => ['pattern' => '/[ا][م]/u', 'color' => '#7A7A7A'], // Grey
    ];

    /**
     * Highlights Tajweed rules in the given text.
     *
     * @param string $text
     * @return string
     */
    protected function highlightTajweed($text)
    {
        foreach ($this->tajweedRules as $ruleName => $rule) {
            $pattern = $rule['pattern'];
            $color = $rule['color'];

            $text = preg_replace_callback($pattern, function ($matches) use ($color, $ruleName) {
                return sprintf(
                    '<span style="color:%s; font-weight:bold;" title="%s">%s</span>',
                    $color,
                    $ruleName,
                    $matches[0]
                );
            }, $text);
        }

        return $text;
    }
}
