<?php
namespace Core\AppsSec\Core\Container;

use Core\Lib\Data\Container\Container;

/**
 * UserContainer.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class RegisterContainer extends Container
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
                'email',
                'empty',
            ]
        ],
        'password' => [
            'type' => 'string',
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
    ];
}
