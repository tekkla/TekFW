<?php
namespace Core\AppsSec\Core\Model\Scheme;

/**
 * GroupScheme.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
return [
    'primary' => 'id_group',
    'table' => 'groups',
    'fields' => [
        'id_group' => [
            'type' => 'int',
            'validate' => [
                'required'
            ]
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
            'type' => 'string'
        ]
    ]
];

