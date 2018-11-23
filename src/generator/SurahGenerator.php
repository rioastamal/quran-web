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
    const VERSION = '1.2';

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
        $footerTemplate = $this->getFooterTemplate();
        $headerTemplate = $this->getHeaderTemplate();
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

            $title = sprintf('Al-Quran Surah %s', $surahJson['name_latin']);
            $keywords = 'al-quran, terjemahan, surah ' . $surahJson['name_latin'];
            $description = sprintf('Al-Quran Surah %s merupakan surah ke-%s yang terdiri dari %s ayat. Lengkap dengan terjemahan Bahasa Indonesia',
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

            $surahHeaderTemplate = str_replace('{{TITLE}}', $title, $headerTemplate);
            $surahHeaderTemplate = str_replace('{{META}}', implode("\n", $metaHeader), $surahHeaderTemplate);

            $surahTemplate = file_get_contents($this->config['templateDir'] . '/surah-layout.html');
            $surahTemplate = str_replace([
                    '{{SURAH_NUMBER}}',
                    '{{SURAH_NAME_LATIN}}',
                    '{{SURAH_NAME_ARABIC}}',
                    '{{TOTAL_AYAH}}',
                    '{{HEADER}}',
                    '{{MENU}}',
                    '{{FOOTER}}'
                ],
                [
                    $surahJson['number'],
                    $surahJson['name_latin'],
                    $surahJson['name'],
                    $surahJson['number_of_ayah'],
                    $surahHeaderTemplate,
                    $menuTemplate,
                    $footerTemplate
                ],
                $surahTemplate
            );

            $ayahTemplate = '';
            if (!in_array($surahNumber, $surahWithoutBasmalah)) {
                $ayahTemplate = $this->getBasmalahTemplate();
            }

            $lang = $this->config['langId'];
            for ($ayat = 1; $ayat <= $surahJson['number_of_ayah']; $ayat++) {
                $ayahTemplate .= $this->getAyahTemplate([
                    'surah_number' => $surahNumber,
                    'surah_name' => $surahJson['name_latin'],
                    'ayah_text' => $surahJson['text'][$ayat],
                    'ayah_number' => $ayat,
                    'ayah_translation' => $surahJson['translations'][$lang]['text'][$ayat]
                ]);
            }
            $surahTemplate = str_replace('{{EACH_AYAH}}', $ayahTemplate, $surahTemplate);
            $surahTemplate = str_replace('{{EACH_GOTO_AYAH}}',
                $this->getGotoAyahTemplate($surahJson['number_of_ayah']), $surahTemplate);
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
        $description = 'QuranWeb adalah Al-Quran online yang ringan dan cepat dengan terjemahan Bahasa Indonesia. Dapat diakses dari perangkat mobile dan komputer desktop.';
        $title = sprintf('Baca Al-Quran Online %s Surah', $surahJson['number']);

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
            '{{HEADER}}',
            '{{FOOTER}}',
            '{{MENU}}'
        ],
        [
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
            '{{HEADER}}',
            '{{FOOTER}}',
            '{{MENU}}'
        ],
        [
            $this->config['appName'],
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
        return <<<AYAH

        <div class="ayah" id="no{$params['ayah_number']}" title="{$params['surah_name']},{$params['surah_number']},{$params['ayah_number']}">
            <div class="ayah-text" dir="rtl"><p>{$params['ayah_text']}<span class="ayah-number" dir="ltr">{$params['ayah_number']}</span></p></div>
            <div class="ayah-toolbar">
                <a class="icon-ayah-toolbar icon-back-to-top" title="Kembali ke atas" href="#">&#x21e7;</a>
                <a class="icon-ayah-toolbar icon-mark-ayah link-mark-ayah" title="Tandai terakhir dibaca" href="#">&#x2713;</a>
            </div>
            <div class="ayah-translation"><p>{$params['ayah_translation']}</p></div>
        </div>

AYAH;
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

        return <<<INDEX

                <li class="surah-index">
                    <a class="surah-index-link" href="{$params['base_url']}/{$params['surah_number']}/">
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
            '{{BASE_URL}}'
        ],
        [
            $this->config['appName'],
            static::VERSION,
            $this->config['baseUrl']
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