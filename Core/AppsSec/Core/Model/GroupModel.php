<?php
namespace Core\AppsSec\Core\Model;

use Core\Lib\Amvc\Model;
use Core\Lib\Security\Group;

/**
 * GroupModel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class GroupModel extends Model
{

    public function getGroup($id = null)
    {
        $container = $this->getContainer();

        if ($id) {

            $group = $this->security->group->getGroupById($id);
            $group['users'] = $this->loadUsers($id);
            $group['permissions'] = $this->loadPermissions($id);

            $container->fill($group);
        }

        return $container;
    }

    public function getGroups($skip_guest = false)
    {
        $data = $this->security->group->getGroups(false, $skip_guest);

        $db = $this->getDbConnector();
        $db->addCallbacks([
            [
                function ($user) {

                    $user['link'] = $this->url('byid', [
                        'controller' => 'user',
                        'action' => 'edit',
                        'id' => $user['id_user']
                    ]);

                    return $user;
                }
            ]
        ]);

        foreach ($data as &$app_groups) {

            foreach ($app_groups as $id_group => &$group) {

                $group['users'] = $this->loadUsers($id_group);

                $group['permissions'] = $this->loadPermissions($id_group);

                $group['link'] = $this->url('edit', [
                    'controller' => 'Group',
                    'id' => $group['id_group']
                ]);
            }
        }

        return $data;
    }

    /**
     * Loads permissions for a given list of group ids
     *
     * @param array $groups
     *            Array of group ids to load the permissions for
     *
     * @param array $groups
     */
    private function loadPermissions($groups = [])
    {
        $db = $this->getDbConnector();

        // Queries without group IDs always results in an empty permission list
        if (empty($groups)) {
            return [];
        }

        // Convert group ID explicit into array
        if (! is_array($groups)) {
            $groups = (array) $groups;
        }

        // Create a prepared string and param array to use in query
        $prepared = $db->prepareArrayQuery('group', $groups);

        // Get and return the permissions
        ;

        $db->qb($qb = [
            'table' => 'groups_permissions',
            'method' => 'SELECT DISTINCT',
            'filter' => 'id_group IN (' . $prepared['sql'] . ')',
            'params' => $prepared['values']
        ]);

        $perms = $db->fetchAll();

        $out = [];

        foreach ($perms as $perm) {
            $out[$perm['app']][$perm['permission']] = $perm;
        }

        return $out;
    }

    private function loadUsers($id_group)
    {
        $db = $this->getDbConnector();

        $db->qb([
            'table' => 'users_groups',
            'alias' => 'ug',
            'fields' => [
                'u.id_user',
                'u.username',
                'IFNULL(u.display_name, u.username) as display_name'
            ],
            'join' => [
                [
                    'users',
                    'u',
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

        return $db->fetchAll();
    }
}



