<?php
return [
    'fields' => [
        'login' => [
            'type' => 'string',
            'validate' => [
                'empty'
            ]
        ],
        'password' => [
            'type' => 'string',
            'validate' => [
                'empty'
            ]
        ],
        'remember' => [
            'type' => 'int'
        ],
        'logged_in' => [
            'type' => 'int'
        ]
    ]
];