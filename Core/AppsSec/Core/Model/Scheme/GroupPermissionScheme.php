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
    'table' => 'groups_permissions',
    'primary' => 'id_group_permission',
    'fields' => [
        'id_group_permission' => [
            'type' => 'int',
        ],
        'permission' => [
            'type' => 'string',
            'validate' => [
                'empty'
            ]
        ],
        'notes' => [
            'type' => 'string',
            'size' => 200
        ]
    ]
];

