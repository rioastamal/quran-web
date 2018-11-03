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

$config =[
    'quranJsonDir' => env('QURAN_JSON_DIR'),
    'buildDir' => BASE_DIR . '/build',
    'publicDir' => BASE_DIR . '/src/public',
    'templateDir' => env('QURAN_TEMPLATE_DIR', BASE_DIR . '/src/generator/template'),
    'beginSurah' => env('QURAN_BEGIN_SURAH', 1),
    'endSurah' => env('QURAN_END_SURAH', 114),
];

$generator = new SurahGenerator($config);
$generator->copyPublic();
$generator->makeSurah();