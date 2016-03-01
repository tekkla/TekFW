<?php
namespace Core\AppsSec\Core\Container;

use Core\Lib\Data\Container\Container;

/**
 * UserContainer.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class UserContainer extends Container
{

    protected $available = [
        'username' => [
            'type' => 'string',
            'validate' => [
                'empty'
            ]
        ],
        'display_name' => [
            'type' => 'string',
        ],
        'password' => [
            'type' => 'string',
            'validate' => [
                'empty'
            ]
        ],
        'groups' => [
            'type' => 'array'
        ]
    ];
}
