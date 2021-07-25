<?php
declare(strict_types = 1);

$config = [
    'includes' => [
        './phpstan-base.neon',
    ],
];

// detect TYPO3 v9, add excludes for symbols/code that was added in TYPO v10+
$config = (function(array $config) {

    $EM_CONF = [];
    $_EXTKEY = 'core';
    require(__DIR__ . '/../.Build/public/typo3/sysext/core/ext_emconf.php');

    $version = $EM_CONF[$_EXTKEY]['version'];

    if (substr($version, 0, 3) === '9.5') {
        $config['includes'][] = './phpstan-baseline-typo3-9.neon';
    }
    return $config;
})($config);

return $config;