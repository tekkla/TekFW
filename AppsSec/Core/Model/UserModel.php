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
                    'core_users_groups',
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

    public function getRegister()
    {
        return [];
    }

    public function createUser($data, $activate)
    {
        $this->addUsernameAndPasswordChecksFromConfig();

        if (! password_verify($data['password'], password_hash($data['password_compare'], PASSWORD_DEFAULT))) {
            $this->addError('password', $this->text('user.error.password.mismatch'));
            $this->addError('password_compare', $this->text('user.error.password.mismatch'));
        }

        $this->validate($data);

        if ($this->hasErrors()) {
            return;
        }

        $db = $this->getDbConnector();

        try {
             return $this->security->users->createUser($data['username'], $data['password'], $activate);
        }
        catch (\Throwable $t) {

            switch ($t->getCode()) {
                case 'user.username.exists':
                    $this->addError('username', $this->text('register.error.name_in_use'));
                    return;
            }
        }
    }

    private function addUsernameAndPasswordChecksFromConfig()
    {
        // Minimum username length set in config?
        $min_length = $this->cfg->get('user.username.min_length');
        $this->scheme['fields']['username']['validate'][] = [
            'min',
            $min_length,
            sprintf($this->text('user.error.username.length'), $min_length)
        ];

        // Regexp check für username set in config?
        $regexp = $this->cfg->get('user.username.regexp');

        if (! empty($regexp)) {
            $this->scheme['fields']['username']['validate'][] = [
                'CustomRegexp',
                $regexp,
                sprintf($this->text('user.error.username.regexp'), $regexp)
            ];
        }

        // Password min and/or maxlength set in config?
        $min_length = $this->cfg->get('user.password.min_length');
        $max_length = $this->cfg->get('user.password.max_length');

        if (! empty($max_length)) {
            $this->scheme['fields']['password']['validate'][] = [
                'range',
                [
                    $min_length,
                    $max_length
                ],
                sprintf($this->text('user.error.password.range'), $min_length, $max_length)
            ];
        }
        else {
            $this->scheme['fields']['password']['validate'][] = [
                'min',
                $min_length,
                sprintf($this->text('user.error.password.min_length'), $min_length)
            ];
        }

        // Password regex check wanted by config?
        $regexp = $this->cfg->get('user.password.regexp');

        if (! empty($regexp)) {
            $this->scheme['fields']['password']['validate'][] = [
                'CustomRegexp',
                $regexp,
                sprintf($this->text('user.error.password.regexp'), $regexp)
            ];
        }
    }
}