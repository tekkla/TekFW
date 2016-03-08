<?php
return [
    'table' => 'users',
    'primary' => 'id_user',
    'fields' => [
        'id_user' => [
            'type' => 'int'
        ],
        'username' => [
            'type' => 'string',
            'validate' => [
                'empty'
            ]
        ],
        'display_name' => [
            'type' => 'string'
        ],
        'password' => [
            'type' => 'string',
            'validate' => [
                'empty'
            ]
        ],
        'state' => [
            'type' => 'int',
            'validate' => [
                'empty',
                [
                    'range',
                    [
                        0,
                        5
                    ]
                ]
            ]
        ]
    ]
];