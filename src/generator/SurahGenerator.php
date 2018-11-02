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
     * @var string
     */
    public $quranJsonDir = null;

    /**
     * @var string
     */
    public $buildDir = null;

    /**
     * @var string
     */
    public $publicDir = null;

    /**
     * @var string
     */
    public $layoutFile = '';

    /**
     * @var int
     */
    public $beginSurah = 1;

    /**
     * @var int
     */
    public $endSurah = 114;

    /**
     * @var string
     */
    public $langId = 'id';

    /**
     * Constructor
     *
     * @param string $quranJsonDir
     * @return void
     */
    public function __construct($quranJsonDir, $layoutFile)
    {
        $this->quranJsonDir = $quranJsonDir;
        $this->layoutFile = $layoutFile;
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
        foreach (range($this->beginSurah, $this->endSurah) as $surahNumber) {
            $jsonFile = $this->quranJsonDir . '/surah/' . $surahNumber . '.json';
            if (!file_exists($jsonFile)) {
                throw new Exception('Can not find json file: ' . $jsonFile);
            }

            $surahJson = json_decode(file_get_contents($jsonFile), $asArray = true);
            if (!isset($surahJson[$surahNumber])) {
                throw new Exception('Can not decode JSON file: ' . $jsonFile);
            }
            $surahJson = $surahJson[$surahNumber];
            $surahDir = $this->buildDir . '/public/' . $surahNumber;

            if (!file_exists($surahDir)) {
                mkdir($surahDir, 0755, $recursive = true);
            }

            $htmlTemplate = file_get_contents($this->layoutFile);
            $htmlTemplate = str_replace([
                    '{{SURAH_NUMBER}}',
                    '{{SURAH_NAME_LATIN}}',
                    '{{SURAH_NAME_ARABIC}}',
                    '{{TOTAL_AYAH}}',
                    '{{TITLE}}',
                    '{{VERSION}}'
                ],
                [
                    $surahJson['number'],
                    $surahJson['name_latin'],
                    $surahJson['name'],
                    $surahJson['number_of_ayah'],
                    sprintf('Al-Quran - Surah %s', $surahJson['name_latin']),
                    static::VERSION
                ],
                $htmlTemplate
            );

            $ayahTemplate = '';
            if (!in_array($surahNumber, $surahWithoutBasmalah)) {
                $ayahTemplate = $this->getBasmalahTemplate();
            }

            for ($ayat = 1; $ayat <= $surahJson['number_of_ayah']; $ayat++) {
                $ayahTemplate .= $this->getAyahTemplate([
                    'ayah_text' => $surahJson['text'][$ayat],
                    'ayah_number' => $ayat,
                    'ayah_translation' => $surahJson['translations'][$this->langId]['text'][$ayat]
                ]);
            }
            $htmlTemplate = str_replace('{{EACH_AYAH}}', $ayahTemplate, $htmlTemplate);
            file_put_contents($surahDir . '/index.html', $htmlTemplate);
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
    public function getAyahTemplate($params)
    {
        return <<<AYAH

        <div class="ayah">
            <div class="ayah-text" dir="rtl"><p>{$params['ayah_text']}<span class="ayah-number" dir="ltr">{$params['ayah_number']}</span></p></div>
            <div class="ayah-translation"><p>{$params['ayah_translation']}</p></div>
        </div>

AYAH;
    }

    /**
     * Copy public directory using shell. We did not want to implements
     * recursive copy.
     *
     * @return void
     */
    public function copyPublic()
    {
        exec('cp -R ' . $this->publicDir . ' ' . $this->buildDir);
    }
}