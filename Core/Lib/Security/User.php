<?php
namespace Core\Lib\Security;

/**
 * Wrapper class to access SMF user information from one point
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 */
class User
{
	/**
	 * User id
	 * @var int
	 */
	private $id_user = 0;

	/**
	 * Username
	 * @var string
	 */
	private $username = '';

	/**
	 * Name to show instead of login
	 * @var string
	 */
	private $display_name = 'Guest';

	/**
	 * Password
	 * @var string
	 */
	private $password = '';

	/**
	 * Usergroups the use is in
	 * @var array
	 */
	private $groups = [];

	/**
	 * User flag: admin
	 * @var unknown
	 */
	private $is_admin = true;

	private $db;

	public function __construct($db, $id_user=0)
	{
		$this->db = $db;

		if ($id_user > 0)
			$this->load($id_user);
	}

	/*+
	 * Checks the user for to be a guest. Is true when user is not logged in
	 * @return boolean
	 */
	public function isGuest()
	{
		return $this->id_user == 0 ? true : false;
	}

	/**
	 * Returns users admin state
	 * @return boolean
	 */
	public function isAdmin()
	{
		return $this->is_admin;
	}

	/**
	 * Returns current users id.
	 * Returns 0 when user ist not logged in.
	 * @return number
	 */
	public function getId()
	{
		return $this->id_user;
	}

	/**
	 * Returns the users login name. Returns false when login is empty.
	 * @return Ambigous <boolean, strin
	 */
	public function getUsername()
	{
		return $this->username ? $this->username : false;
	}

	/**
	 * Returns the users current password. Returns false when password is empty.
	 * @return Ambigous <boolean, strin
	 */
	public function getPassword()
	{
		return $this->password ? $this->password : false;
	}

	/**
	 * Returns groups user is in
	 */
	public function getGroups()
	{
		return $this->groups;
	}

	/*+
	 * loads user from DB
	 */
	public function load($id_user)
	{
		$this->id_user = $id_user;

		$this->db->query('SELECT username, password, display_name, groups FROM {db_prefix}users WHERE id_user=:id_user');
		$this->db->bindValue(':id_user', $id_user);

		if ($this->db->execute())
		{
			$row = $this->db->single();

			if ($row['groups'])
				$this->groups = unserialize($row['groups']);

			if (in_array('1', $this->groups))
				$this->is_admin = true;

			$this->username = $row['username'];
			$this->display_name = $row['display_name'];
			$this->password = $row['password'];
		}
	}
}
