<?php
require __DIR__ . '/SurahGenerator.php';
define('BASE_DIR', realpath(__DIR__ . '/../..'));

function env($envName, $default = null)
{
    if (isset($_SERVER[$envName])) {
        return $_SERVER[$envName];
    }

    return $default;
}

$config = [
    'quranJsonDir' => env('QURAN_JSON_DIR'),
    'baseUrl' => env('QURAN_BASE_URL'),
    'baseMurottalUrl' => env('QURAN_BASE_MUROTTAL_URL'),
    'buildDir' => BASE_DIR . '/build',
    'publicDir' => BASE_DIR . '/src/public',
    'templateDir' => env('QURAN_TEMPLATE_DIR', BASE_DIR . '/src/generator/template'),
    'beginSurah' => env('QURAN_BEGIN_SURAH', 1),
    'endSurah' => env('QURAN_END_SURAH', 114),
    'githubProjectUrl' => env('QURAN_GITHUB_PROJECT_URL', 'https://github.com/rioastamal/quran-web'),
    'rawHtmlMeta' => env('QURAN_RAW_HTML_META'),
    'ogImageUrl' => env('QURAN_OG_IMAGE_URL', 'https://s3-ap-southeast-1.amazonaws.com/quranweb/quranweb-1024.png')
];

echo "Generating website...";
try {
    $config['baseMurottalUrl'] = $config['baseMurottalUrl'] ? $config['baseMurottalUrl'] : 'https://everyayah.com/data';
    $generator = new SurahGenerator($config);
    $generator->copyPublic();
    $generator->makeSurah();
    echo "done.\n";
} catch (Exception $e) {
    echo "FAIL.\n";
    printf("Error: %s\n", $e->getMessage());
}