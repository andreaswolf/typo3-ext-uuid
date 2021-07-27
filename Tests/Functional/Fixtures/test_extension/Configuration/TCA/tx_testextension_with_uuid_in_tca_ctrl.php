<?php

return [
    'ctrl' => [
        'title' => 'Table that uuid=true in TCA ctrl',
        'delete' => 'deleted',
        'crdate' => 'crdate',
        'uuid' => true,
    ],
    'columns' => [
        'some_input_field' => [
            'exclude' => true,
            'label' => 'Random input field',
            'config' => [
                'type' => 'input',
            ]
        ]
    ],
    'types' => [
        0 => [
            'showitem' => 'some_input_field'
        ],
    ],
];
