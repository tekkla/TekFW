<?php
namespace Core\AppsSec\Core\Container;

use Core\Lib\Data\Container;

/**
 *
 * @author Michael
 *
 */
class UserContainer extends Container
{

    protected $available = [
        'id_user' => [
            'type' => 'int',
            'primary' => true
        ],
        'username' => [
            'type' => 'string',
            'size' => 255,
            'validate' => [
                'empty',
                [
                    'min',
                    [
                        5,
                        true
                    ]
                ],
                [
                    'max',
                    [
                        255,
                        true
                    ]
                ]
            ]
        ],
        'password' => [
            'type' => 'string',
            'size' => 255,
            'validate' => [
                'empty',
                [
                    'min',
                    [
                        8,
                        true
                    ]
                ],
                [
                    'max',
                    [
                        255,
                        true
                    ]
                ]
            ]
        ],
        'display_name' => [
            'type' => 'string',
            'size' => 255,
            'validate' => [
                'empty',
                [
                    'min',
                    [
                        5,
                        true
                    ]
                ],
                [
                    'max',
                    [
                        255,
                        true
                    ]
                ]
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
        ],
    ];

    public function Index()
    {
        $this->use = [
            'id_user',
            'username',
            'password',
            'display_name',
            'state',
        ];
    }
}
