<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'UUID test extension',
    'description' => 'Provides example tables for UUID handling',
    'category' => 'misc',
    'state' => 'alpha',
    'author' => 'Andreas Wolf',
    'author_email' => 'dev@a-w.io',
    'version' => '0.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-9.5.99',
            'event_dispatcher' => '0.0.0',
        ],
    ],
];
