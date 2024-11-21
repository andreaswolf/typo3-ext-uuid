<?php

defined('TYPO3') or die();

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tsparser.php']['preParseFunc']['uuid'] =
    \AndreasWolf\Uuid\TypoScript\UuidPreprocessor::class . '->resolveUuidInTypoScript';
