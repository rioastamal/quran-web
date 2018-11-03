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
    const VERSION = '1.0';

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
        ];
        $this->config = $config + $defaultConfig;

        $requiredConfig = ['quranJsonDir', 'buildDir', 'publicDir', 'templateDir'];
        foreach ($requiredConfig as $required) {
            if (!isset($this->config[$required])) {
                throw new InvalidArgumentException('Missing config: ' . $required);
            }
        }

        if (!file_exists($this->config['quranJsonDir'])) {
            throw new InvalidArgumentException('Can not find quran-json dir: ' . $this->config['quranJsonDir']);
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
        $css = file_get_contents($this->config['templateDir'] . '/style.css');
        $javascript = file_get_contents($this->config['templateDir'] . '/script.js');

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

            $surahTemplate = file_get_contents($this->config['templateDir'] . '/surah-layout.html');
            $surahTemplate = str_replace([
                    '{{SURAH_NUMBER}}',
                    '{{SURAH_NAME_LATIN}}',
                    '{{SURAH_NAME_ARABIC}}',
                    '{{TOTAL_AYAH}}',
                    '{{TITLE}}',
                    '{{STYLE}}',
                    '{{SCRIPT}}',
                    '{{VERSION}}'
                ],
                [
                    $surahJson['number'],
                    $surahJson['name_latin'],
                    $surahJson['name'],
                    $surahJson['number_of_ayah'],
                    sprintf('Al-Quran - Surah %s', $surahJson['name_latin']),
                    $css,
                    $javascript,
                    static::VERSION
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
                    'ayah_text' => $surahJson['text'][$ayat],
                    'ayah_number' => $ayat,
                    'ayah_translation' => $surahJson['translations'][$lang]['text'][$ayat]
                ]);
            }
            $surahTemplate = str_replace('{{EACH_AYAH}}', $ayahTemplate, $surahTemplate);
            file_put_contents($surahDir . '/index.html', $surahTemplate);

            $indexSurahTemplate = $this->getSurahIndexTemplate([
                'base_url' => 'http://localhost:9999',
                'surah_number' => $surahNumber,
                'surah_name' => $surahJson['name'],
                'surah_name_latin' => $surahJson['name_latin'],
                'number_of_ayah' => $surahJson['number_of_ayah']
            ]);
            $indexTemplate = str_replace('{{SURAH_INDEX}}', $indexSurahTemplate, $indexTemplate);
        }

        $indexFile = $this->config['buildDir'] . '/public/index.html';
        $indexTemplate = str_replace([
            '{{TITLE}}',
            '{{STYLE}}',
            '{{SCRIPT}}'
        ],
        [
            'Daftar Surah dalam Al-Quran',
            $css,
            $javascript
        ], $indexTemplate);
        file_put_contents($indexFile, $indexTemplate);
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
    public function getAyahTemplate($params)
    {
        return <<<AYAH

        <div class="ayah" id="no{$params['ayah_number']}">
            <div class="ayah-text" dir="rtl"><p>{$params['ayah_text']}<span class="ayah-number" dir="ltr">{$params['ayah_number']}</span></p></div>
            <div class="ayah-translation"><p>{$params['ayah_translation']}</p></div>
        </div>

AYAH;
    }

    public function getSurahIndexTemplate($params)
    {
        $tag = '{{SURAH_INDEX}}';

        if ($params['surah_number'] == $this->config['endSurah']) {
            $tag = '';
        }

        return <<<INDEX

                <li class="surah-index">
                    <a class="surah-index-link" href="{$params['base_url']}/{$params['surah_number']}">
                        <span class="surah-index-name">{$params['surah_name_latin']} - {$params['surah_name']}</span>
                        <span class="surah-index-ayah">{$params['number_of_ayah']} Ayat</span>
                        <span class="surah-index-number">{$params['surah_number']}</span>
                    </a>
                </li>

                {$tag}
INDEX;
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