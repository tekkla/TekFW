<?php
namespace Core\Lib\Security;

use Core\Lib\Data\Connectors\Db\Db;

/**
 * Permission.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Permission
{

    /**
     *
     * @var array
     */
    private $permissions = [];

    /**
     *
     * @var Db
     */
    private $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    /**
     * Adds one or more permissions to permissions list
     *
     * @param string $app_name
     *            Name of permission related app
     * @param array $permissions
     *            One or more permissions to add
     */
    public function addPermission($app_name, $permissions = [])
    {
        if ($permissions) {

            if (! is_array($permissions)) {
                $permissions = (array) $permissions;
            }

            foreach ($permissions as $perm) {
                $this->permissions[] = $app_name . '_' . $perm;
            }
        }
    }

    /**
     * Returns all or app related permissions
     *
     * @param string $app
     *
     * @return array
     */
    public function getPermissions($app = '')
    {
        return $app ? $this->permissions[$app] : $this->permissions;
    }

    /**
     *
     * @param array $groups
     */
    public function loadPermission($groups = [])
    {
        // Queries without group IDs always results in an empty permission list
        if (empty($groups)) {
            return [];
        }

        // Convert group ID explicit into array
        if (! is_array($groups)) {
            $groups = (array) $groups;
        }

        // Create a prepared string and param array to use in query
        $prepared = $this->db->prepareArrayQuery('group', $groups);

        // Get and return the permissions
        $query = [
            'table' => 'permissions',
            'method' => 'SELECT DISTINCT',
            'filter' => 'id_group IN (' . $prepared['sql'] . ')',
            'params' => $prepared['values']
        ];

        $this->db->qb($query);

        return $this->db->column(2);
    }
}
