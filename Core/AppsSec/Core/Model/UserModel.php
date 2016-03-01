<?php
namespace Core\AppsSec\Core\Model;

use Core\Lib\Amvc\Model;
use Core\Lib\Security\User;

/**
 * UserModel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class UserModel extends Model
{

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

        $container = $this->getContainer();

        $container['id_user'] = $id_user;
        $container['username'] = $username;
        $container['display_name'] = $display_name;
        $container['groups'] = $groups;

        return $container;
    }

    public function getList($field = 'display_name', $needle = '%', $limit = 100, array $callbacks = [])
    {
        $qb = [
            'table' => $this->table,
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
}