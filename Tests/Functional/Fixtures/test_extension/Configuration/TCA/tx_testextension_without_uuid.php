<?php

return [
    'ctrl' => [
        'title' => 'Table that has no UUID field',
        'delete' => 'deleted',
        'crdate' => 'crdate',
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
