<?php
namespace Core\Lib\Security;

use Core\Lib\Data\Database;

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
	 * @var Database
	 */
	private $db;

	/**
	 *
	 * @var Permission
	 */
	private $permission;

	public function __construct(Database $db, Permission $permission, $id_user = 0)
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
	 * Returns current users id. Returns 0 when user ist not logged in.
	 *
	 * @return number
	 */
	public function getId()
	{
		return $this->id_user;
	}

	/**
	 * Returns the users login name. Returns false when login is empty.
	 *
	 * @return string|boolean
	 */
	public function getUsername()
	{
		return $this->username ? $this->username : false;
	}

	/**
	 * Returns the users current password. Returns false when password is empty.
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
	public function load($id_user, $force=false)
	{
		// Do not load the user more than once
		if ($this->id_user == $id_user && $force == false) {
			return;
		}

		$this->id_user = $id_user;

		$this->db->query('SELECT username, password, display_name, groups FROM {db_prefix}users WHERE id_user=:id_user');
		$this->db->bindValue(':id_user', $id_user);

		if ($this->db->execute()) {

			$row = $this->db->single();

			$this->username = $row['username'];
			$this->display_name = $row['display_name'];
			$this->password = $row['password'];

			// Load groups the user is in
			$this->db->query('SELECT id_group FROM {db_prefix}users_groups WHERE id_user=:id_user');
			$this->db->bindValue(':id_user', $id_user);

			if ($this->db->execute()) {
				$this->groups = $this->db->column();
			}

			// Load user permissions based on groups of the user
			$this->perms = $this->permission->loadPermission($this->groups);

			// Is the user an admin?
			if (in_array('core_admin', $this->perms)) {
				$this->is_admin = true;
			}
		}
	}
}
