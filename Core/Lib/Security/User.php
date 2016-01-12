<?php
namespace Core\Lib\Security;

use Core\Lib\Data\Connectors\Db\Db;
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
    private $username = '';

    /**
     * Name to show instead of login
     *
     * @var string
     */
    private $display_name = 'Guest';

    /**
     * Password
     *
     * @var string
     */
    private $password = '';

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
     * @var Permission
     */
    private $permission;

    public function __construct(Db $db, Permission $permission, $id_user = 0)
    {
        $this->db = $db;
        $this->permission = $permission;

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
     * Returns the users login name.
     * Returns false when login is empty.
     *
     * @return string|boolean
     */
    public function getUsername()
    {
        return $this->username ? $this->username : false;
    }

    /**
     * Returns the users current password.
     * Returns false when password is empty.
     *
     * @return string|boolean
     */
    public function getPassword()
    {
        return $this->password ? $this->password : false;
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
     * Returns the users permissions
     *
     * @return array
     */
    public function getPermissions()
    {
        return $this->perms;
    }

    /**
     * Loads user from DB.
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
            'tbl' => 'users',
            'field' => [
                'username',
                'password',
                'display_name'
            ],
            'filter' => 'id_user=:id_user',
            'params' => [
                ':id_user' => $id_user
            ]
        ]);

        $data =  $this->db->single();

        if ($data) {

            $this->username = $data['username'];
            $this->display_name = $data['display_name'];
            $this->password = $data['password'];

            $this->db->qb([
                'tbl' => 'users_groups',
                'fields' => 'id_group',
                'filter' => 'id_user=:id_user',
                'params' => [
                    ':id_user' => $id_user
                ]
            ]);

            $this->groups = $this->db->column();

            // Load user permissions based on groups of the user
            $this->perms = $this->permission->loadPermission($this->groups);

            // Is the user an admin?
            if (in_array('core_admin', $this->perms)) {
                $this->is_admin = true;
            }
        }
    }

    /**
     * Creates a new user
     *
     * Uses given username and password and returns it's user id.
     * Optional state flag to activate user on creation.
     *
     * Given password will be hashed by password_hash($password, PASSWORD_DEFAULT) by default.
     *
     * @param string $username Username
     * @param string $password Password
     * @param boolean $state Optional: Stateflag. 0=inactive | 1=active (default: 0)
     * @param boolean $password_hash Optional: Flag to activate password hashing by using password_hash (Default: true)
     *
     * @return integer
     */
    public function create($username, $password, $state=0, $password_hash=true)
    {
        $data = $this->db->adapter->getContainer(true);

        $data['username'] = $username;

        if ($password_hash == true) {
            $password = password_hash($password, PASSWORD_DEFAULT);
        }

        $data['password'] = $password;
        $data['state'] = (int) $state;

        $this->db->qb([
            'table' => 'users',
            'data' => $data
        ], true);

        return $this->db->lastInsertId();
    }
}
