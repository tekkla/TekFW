<?php
namespace Core\AppsSec\Core\Container;

use Core\Lib\Data\Container;

/**
 * SecurityContainer.php
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class SecurityContainer extends Container
{
    protected $available = [
        'login' => [
            'type' => 'string',
            'validate' => [
                'empty',
            ]
        ],
        'password' => [
            'type' => 'string',
            'validate' => [
                'empty',
            ]
        ],
        'remember' => [
            'type' => 'int',
        ],
        'logged_in' => [
            'type' => 'int',
        ]
    ];
}
