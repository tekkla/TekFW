<?php
namespace Core\AppsSec\Core\Container;

use Core\Lib\Data\Container\Container;

/**
 * GroupContainer.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class GroupContainer extends Container
{

    protected $available = [
        'id_group' => [
            'type' => 'int',
            'primary' => true
        ],
        'title' => [
            'type' => 'string',
            'size' => 200,
            'validate' => [
                'empty'
            ]
        ],
        'display_name' => [
            'type' => 'string',
            'size' => 200,
            'validate' => [
                'empty'
            ]
        ],
        'description' => [
            'type' => 'string',
        ],
        'permissions' => [
            'type' => 'array',
        ]
    ];
}
