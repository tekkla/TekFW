<?php
namespace AppsSec\Core\Model;

use Core\Amvc\Model;
use Core\Security\User;

/**
 * UserModel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class UserModel extends Model
{

    protected $scheme = [
        'table' => 'core_users',
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

    private $table = 'users';

    public function getEdit(User $user, $id_user = null)
    {
        if ($id_user) {
            $user->load($id_user);
            $id_user = $user->getId();
            $username = $user->getUsername();
            $display_name = $user->getDisplayname();
            $groups = $user->getGroups();
        }
        else {
            $user->load($id_user);
            $username = '';
            $display_name = '';
            $groups = [];
        }

        return [
            'id_user' => $id_user,
            'username' => $username,
            'display_name' => $display_name,
            'groups' => $groups
        ];
    }

    public function getList($field = 'display_name', $needle = '%', $limit = 100, array $callbacks = [])
    {
        $qb = [
            'table' => $this->scheme['table'],
            'filter' => $field . ' LIKE :' . $field,
            'params' => [
                ':' . $field => $needle
            ]
        ];

        if ($limit) {
            $qb['limit'] = 100;
        }

        $db = $this->getDbConnector();

        if ($callbacks) {
            $db->addCallbacks($callbacks);
        }

        $db->qb($qb);

        return $db->all();
    }

    public function loadUsersByGroupId($id_group)
    {
        $db = $this->getDbConnector();

        $db->qb([
            'table' => $this->scheme['table'],
            'alias' => 'u',
            'fields' => [
                'u.id_user',
                'u.username',
                'IFNULL(u.display_name, u.username) as display_name'
            ],
            'join' => [
                [
                    'users_groups',
                    'ug',
                    'INNER',
                    'u.id_user=ug.id_user'
                ]
            ],
            'filter' => 'ug.id_group = :id_group',
            'params' => [
                ':id_group' => $id_group
            ],
            'order' => 'display_name'
        ]);

        return $db->all();
    }
}