<?php
namespace Core\Security;

use Core\Data\Connectors\Db\Db;

/**
 * Wrapper class to access SMF user information from one point
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 */
class User
{

    /**
     * User id
     *
     * @var int
     */
    private $id_user = 0;

    /**
     * Username
     *
     * @var string
     */
    private $username = 'Guest';

    /**
     * Name to show instead of login
     *
     * @var string
     */
    private $display_name = 'Guest';

    /*
     * +
     * Permissions the user owns
     *
     * @var array
     */
    private $perms = [];

    /**
     * Usergroups the use is in
     *
     * @var array
     */
    private $groups = [];

    /**
     * User flag: admin
     *
     * @var unknown
     */
    private $is_admin = false;

    /**
     *
     * @var Db
     */
    private $db;

    /**
     *
     * @param Db $db
     * @param number $id_user
     */
    public function __construct(Db $db, $id_user = 0)
    {
        $this->db = $db;

        if ($id_user > 0) {
            $this->load($id_user);
        }
    }

    /*
     * Checks the user for to be a guest. Is true when user is not logged in.
     *
     * @return boolean
     */
    public function isGuest()
    {
        return $this->id_user == 0 ? true : false;
    }

    /**
     * Returns users admin state
     *
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->is_admin;
    }

    /**
     * Returns current users id.
     * Returns 0 when user ist not logged in.
     *
     * @return number
     */
    public function getId()
    {
        return $this->id_user;
    }

    /**
     * Returns the users login name
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns the users displayname
     *
     * @return string
     */
    public function getDisplayname()
    {
        return $this->display_name;
    }

    /**
     * Returns groups user is in
     *
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Returns informations of the current user like username, display_name, groups, permission, admin/guest state etc
     *
     * @return array
     */
    public function getUserInfo()
    {
        return [
            'username' => $this->username,
            'display_name' => $this->display_name,
            'groups' => $this->groups,
            'perms' => $this->perms,
            'is_admin' => $this->isAdmin(),
            'is_guest' => $this->isGuest()
        ];
    }

    /**
     * Returns the users permissions
     *
     * @param string $app
     *            Optional name of app to get the permissions from. Without this name all existing permissions will be returned.
     *
     * @return array
     */
    public function getPermissions($app = '')
    {
        if (array_key_exists($app, $this->perms)) {
            return $this->perms[$app];
        }

        return $this->perms;
    }

    /**
     * Loads user from DB.
     *
     * Takes care about not to load a user more than once
     *
     * @param int $id_user
     * @param boolean $force
     */
    public function load($id_user, $force = false)
    {

        // Do not load the user more than once
        if ($this->id_user == $id_user && $force == false) {
            return;
        }

        $this->id_user = $id_user;

        $this->db->qb([
            'table' => 'core_users',
            'field' => [
                'username',
                'display_name'
            ],
            'filter' => 'id_user=:id_user',
            'params' => [
                ':id_user' => $id_user
            ]
        ]);

        $data = $this->db->single();

        if ($data) {

            $this->username = $data['username'];

            // Use username as display_name when there is no display_name for this user
            $this->display_name = empty($data['display_name']) ? $data['username'] : $data['display_name'];

            // Load the groups the user is in
            $this->db->qb([
                'table' => 'core_users_groups',
                'fields' => 'id_group',
                'filter' => 'id_user=:id_user',
                'params' => [
                    ':id_user' => $id_user
                ]
            ]);

            $this->groups = $this->db->column();

            // Load user permissions based on groups of the user
            $this->loadPermissions();

            // Is the user an admin?
            if (isset($this->perms['Core']) && in_array('admin', $this->perms['Core'])) {
                $this->is_admin = true;
            }

            $_SESSION['Core']['user'] = [
                'id' => $id_user,
                'username' => $this->username,
                'display_name' => $this->display_name,
                'is_admin' => $this->is_admin,
                'groups' => $this->groups,
                'permissions' => $this->perms
            ];
        }
    }

    /**
     * Checks user access by permissions
     *
     * @param array $perms
     * @param boolean $force
     *
     * @return boolean
     */
    public function checkAccess($perms = [], $force = false, $app = 'Core')
    {
        // Guests are not allowed by default
        if ($this->isGuest()) {
            return false;
        }

        // Allow access to all users when perms argument is empty
        if (empty($perms)) {
            return true;
        }

        // Administrators are supermen :P
        if ($this->isAdmin()) {
            return true;
        }

        // Explicit array conversion of perms arg
        if (! is_array($perms)) {
            $perms = (array) $perms;
        }

        // User has the right to do this?
        if (count(array_intersect($perms, $this->getPermissions($app))) > 0) {
            return true;
        }

        // You aren't allowed, by default.
        return false;
    }

    /**
     * Security method to log suspisious actions and start banning process.
     *
     * @param string $msg
     *            Message to log
     * @param boolean|int $ban
     *            Set this to the number of tries the user is allowed to do other suspicious things until he gets
     *            banned.
     *
     * @return Security
     */
    public function logSuspicious($msg, $ban = false)
    {
        $this->logging->suspicious($msg);

        return $this;
    }

    /**
     * Loads permissions for a given list of group ids
     *
     * @param array $groups
     *            Array of group ids to load the permissions for
     *
     * @param array $groups
     */
    private function loadPermissions()
    {
        // Queries without group IDs always results in an empty permission list
        if (empty($this->groups)) {
            return [];
        }

        // Create a prepared string and param array to use in query
        $prepared = $this->db->prepareArrayQuery('group', $this->groups);

        // Get and return the permissions
        $qb = [
            'table' => 'core_groups_permissions',
            'fields' => [
                'app',
                'permission'
            ],
            'method' => 'SELECT DISTINCT',
            'filter' => 'id_group IN (' . $prepared['sql'] . ')',
            'params' => $prepared['values']
        ];

        $this->db->qb($qb);

        $perms = $this->db->all();

        foreach ($perms as $perm) {
            $this->perms[$perm['app']][] = $perm['permission'];
        }
    }
}
