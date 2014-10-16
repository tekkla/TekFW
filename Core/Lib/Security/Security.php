<?php
namespace Core\Lib\Security;

use Core\Lib\Session;
use Core\Lib\Cfg;
use Core\Lib\Cookie;
use Core\Lib\Data\Database;

/**
 * Class: Security
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 */
class Security
{

	/**
	 * Cookiename
	 *
	 * @var string
	 */
	private $cookie_name = 'tekfw98751822';

	/**
	 * Pepper instead of salt.
	 * I like it more!
	 *
	 * @var string
	 */
	private $pepper = 'Sfgg$%fsa""sdfsddf#123WWdä,-$';

	/**
	 * Days until cookies expire
	 *
	 * @var int
	 */
	private $days = 30;

	/**
	 * Timestamp for cookie expire date
	 *
	 * @var int
	 */
	private $expire_time = 0;

	/**
	 *
	 * @var Database
	 */
	private $db;

	/**
	 *
	 * @var Cookie
	 */
	private $cookie;

	/**
	 *
	 * @var User
	 */
	private $user;

	/**
	 *
	 * @var Cfg
	 */
	private $cfg;

	/**
	 *
	 * @var Session
	 */
	private $session;

	/**
	 *
	 * @var Group
	 */
	private $group;

	/**
	 *
	 * @var Permission
	 */
	private $permission;

	/**
	 */
	public function __construct(
		Database $db,
		Cfg $cfg,
		Session $session,
		Cookie $cookie,
		User $user,
		Group $group,
		Permission $permission
	)
	{
		$this->db = $db;
		$this->cfg = $cfg;
		$this->session = $session;
		$this->cookie = $cookie;
		$this->user = $user;
		$this->group = $group;
		$this->permission = $permission;
	}

	/*
	 * @var
	 */

	/**
	 * Initiates security model.
	 * Sets object parameter by using config values.
	 * Tries to autologin the current user
	 */
	public function init()
	{
		// Set parameter
		if ($this->cfg->exists('Core', 'cookie_name')) {
			$this->cookie_name = $this->cfg->get('Core', 'cookie_name');
		}

		if ($this->cfg->exists('Core', 'security_pepper')) {
			$this->pepper = $this->cfg->get('Core', 'security_pepper');
		}

		if ($this->cfg->exists('Core', 'security_autologin_expire_days')) {
			$this->days = $this->cfg->get('Core', 'security_autologin_expire_days');
		}

		// Try autologin
		$this->doAutoLogin();
	}

	/**
	 * Sets the cookie name to be used in autologin cookie name
	 *
	 * @param string $cookie_name
	 * @return \Core\Lib\Security
	 */
	public function setCookieName($cookie_name)
	{
		$this->cookie_name = $cookie_name;
		return $this;
	}

	/**
	 * Sets custom pepper string used to create usertoken
	 *
	 * @param string $pepper
	 * @return \Core\Lib\Security
	 */
	public function setPepper($pepper)
	{
		$this->pepper = $pepper;
		return $this;
	}

	/**
	 * Sets the number of days the login cookie should be valid when user requests autologin.
	 *
	 * @param int $days
	 * @return \Core\Lib\Security
	 */
	public function setDaysUntilCookieExpires($days)
	{
		$this->days = (int) $days;

		// Auto calculate expiretime
		$this->generateExpireTime();

		return $this;
	}

	/**
	 * Returns the set cookiename
	 *
	 * @return string
	 */
	public function getCookieName()
	{
		return $this->cookie_name;
	}

	/**
	 * Returns set pepper string.
	 *
	 * @return string
	 */
	public function getPepper()
	{
		return $this->pepper;
	}

	/**
	 * Returns the number of days the autologin cookie stys valid
	 *
	 * @return number
	 */
	public function getDaysUntilCookieExpires()
	{
		return $this->days;
	}

	/**
	 * Validates the provided data against user data to perform user login.
	 * Offers option to activate autologin.
	 *
	 * @param unknown $login Login name
	 * @param unknown $password Password to validate
	 * @param boolean $remember_me Option to activate autologin
	 * @return boolean|mixed
	 */
	public function login($username, $password, $remember_me = true)
	{
		// Empty username or password
		if (! trim($username) || ! trim($password)) {
			return false;
		}

		// Try to load user from db
		$this->db->query('SELECT id_user, password FROM {db_prefix}users WHERE username = :username LIMIT 1');
		$this->db->bindValue(':username', $username);
		$this->db->execute();

		$login = $this->db->single(\PDO::FETCH_NUM);

		// No user found => login failed
		if (! $login) {
			return false;
		}

		// Password ok?
		if (password_verify($password . $this->pepper, $login[1])) {
			// Needs hash to be updated?
			if (password_needs_rehash($login[1], PASSWORD_DEFAULT)) {
				$this->db->query('UPDATE {db_prefix}users SET password = :hash WHERE id_user = :id_user');
				$this->db->bindValue(':hash', password_hash($password . $this->pepper, PASSWORD_DEFAULT));
				$this->db->bindValue(':id_user', $login[0]);
				$this->db->execute();
			}

			// Store essential userdata in session
			$this->session->set('logged_in', true);
			$this->session->set('id_user', $login[0]);

			// Remember for autologin?
			if ($remember_me) {
				$this->setAutoLoginCookies($login[0]);
			}

			// Login is ok, return user id
			return $login[0];
		} else {
			return false;
		}
	}

	/**
	 * Logout of the user and clean up autologin cookies
	 */
	public function logout()
	{
		// Clean up session
		$this->session->set('autologin_failed', true);
		$this->session->set('id_user', 0);
		$this->session->set('logged_in', false);

		// Calling logout means to revoke autologin cookies
		$this->cookie->remove($this->cookie_name . 'U');
		$this->cookie->remove($this->cookie_name . 'T');
	}

	/**
	 * Tries to autologin the user by comparing token stored in cookie with a generated token created of user credentials.
	 *
	 * @return boolean
	 */
	public function doAutoLogin()
	{
		// Cookienames for user id and token
		$cookie_user = $this->cookie_name . 'U';
		$cookie_token = $this->cookie_name . 'T';

		// No autologin when either user or token cookie not present or autologin already failed
		if (! $this->cookie->exists($cookie_user) || ! $this->cookie->exists($cookie_token) || $this->session->exists('autologin_failed')) {
			// Remove fragments/all of autologin cookies
			$this->cookie->remove($cookie_user);
			$this->cookie->remove($cookie_token);

			// Remove the flag which forces the log off
			$this->session->remove('autologin_failed');

			return false;
		}

		// Read user id cookie
		$id_user = base64_decode($this->cookie->get($cookie_user));

		// Compare genreated token with token stored in cookie and set user as logged in on equal
		$token = $this->generateUserToken($id_user);

		// Cookie successful validated
		if ($token == $this->cookie->get($cookie_token)) {

			// Refresh autologin cookie so the user stays logged in until he nver comes back
			$this->setAutoLoginCookies($id_user);

			// Login user, set session flags and return true
			$this->session->set('logged_in', true);
			$this->session->set('id_user', $id_user);

			// Remove autologin flag flag
			$this->session->remove('autologin_failed');

			return true;
		}

		// ## Reaching this point means autologin validation failed

		// Remove user and tooken cookie
		$this->cookie->remove($cookie_user);
		$this->cookie->remove($cookie_token);

		// Set flag that autologin failed
		$this->session->set('autologin_failed', true);

		// Set logged in flag explicit to false
		$this->session->set('logged_in', false);

		// Set id of user explicit to 0 (guest)
		$this->session->set('id_user', 0);

		// sorry, no autologin
		return false;
	}

	/**
	 * Set auto login cookies with user id and generated token
	 *
	 * @param int $id_user
	 * @throws Error
	 * @todo Take care of cookie parameters
	 */
	private function setAutoLoginCookies($id_user)
	{
		// Cast user id explicite integer
		$id_user = (int) $id_user;

		// Check type of $id_user to be integer and throw error when not integer
		if (! $id_user) {
			Throw new \InvalidArgumentException('User id is empty or zero.');
		}

		// Check for empty expire time and generate time if it is empty
		if (! $this->expire_time) {
			$this->generateExpireTime();
		}

		// Expiretime for both cookies
		$this->cookie->setExpire($this->expire_time);

		// User id cookie
		$this->cookie->setName($this->cookie_name . 'U');
		$this->cookie->setValue(base64_encode($id_user));
		$this->cookie->set();

		// User token cookie
		$this->cookie->setName($this->cookie_name . 'T');
		$this->cookie->setValue($this->generateUserToken($id_user));
		$this->cookie->set();
	}

	/**
	 * Generates a hashed user token based on userdata
	 * @param unknown $id_user
	 * @return string
	 */
	private function generateUserToken($id_user)
	{
		// Get userinfo
		$this->user->load($id_user);

		// Unique pieces of our user
		$pieces = [
			$id_user,
			$this->pepper,
			$this->user->getUsername(),
			$this->user->getPassword(),
			implode(',', $this->user->getGroups()),
		];

		// Build hash from combinded data
		$token = md5(implode('|', $pieces));

		return $token;
	}

	/**
	 * Returns login state of current user
	 * @return boolean
	 */
	public function loggedIn()
	{
		return $this->session->get('logged_in') == true && $this->session->get('id_user') > 0 ? true : false;
	}

	/**
	 * Generates the expiring timestamp for cookies
	 *
	 * @return number
	 */
	private function generateExpireTime()
	{
		// Create expire date of autologin
		return $this->expire_time = time() + 3600 * 24 * $this->days;
	}

	/**
	 * Returns the list of permissions the current user owns
	 *
	 * @return array
	 */
	public function getPermissions()
	{
		return $this->user->getPermissions();
	}

	public function getGroups()
	{
		return $this->group->getGroups();
	}

	/**
	 * Checks user access by permissions
	 *
	 * @param unknown $perms
	 * @param string $force
	 *
	 * @return boolean
	 */
	public function checkAccess($perms = [], $force = false)
	{
		// Guests are not allowed by default
		if ($this->user->isGuest()) {
			return false;
		}

		// Allow access to all users when perms argument is empty
		if (empty($perms)) {
			return true;
		}

		// Administrators are supermen :P
		if ($this->user->isAdmin()) {
			return true;
		}

		// Explicit array conversion of perms arg
		if (! is_array($perms)) {
			$perms = (array) $perms;
		}

		// User has the right to do this?
		if (count(array_intersect($perms, $this->user->getPermissions())) > 0) {
			return true;
		}

		// You aren't allowed, by default.
		return false;
	}
}
