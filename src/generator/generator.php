<?php
require __DIR__ . '/SurahGenerator.php';
define('BASE_DIR', __DIR__ . '/../..');

$requiredEnv = ['QURAN_JSON_DIR'];
foreach ($requiredEnv as $required) {
    if (!isset($_SERVER[$required])) {
        throw new Exception(sprintf('Missing environment name %s.', $required));
    }
}

$layoutFile = BASE_DIR . '/src/generator/surah-layout.html';
if (isset($_SERVER['QURAN_SURAH_LAYOUT_FILE'])) {
    $layoutFile = $_SERVER['QURAN_SURAH_LAYOUT_FILE'];
}

$endSurah = 114;
if (isset($_SERVER['QURAN_END_SURAH'])) {
    $endSurah = $_SERVER['QURAN_END_SURAH'];
}

$generator = new SurahGenerator($_SERVER['QURAN_JSON_DIR'], $layoutFile);
$generator->endSurah = $endSurah;
$generator->buildDir = BASE_DIR . '/build';
$generator->publicDir = BASE_DIR . '/src/public';
$generator->copyPublic();
$generator->makeSurah();