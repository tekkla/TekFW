<?php
namespace Core\Lib;

if (!defined('TEKFW'))
	die('Cannot run without TekFW framework...');

/**
 * Class handles cookie related stuff
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014 by author
 * @version 1.0.0
 * @license MIT
 */
class Cookie
{
	/**
	 * Cookie name
	 * @var string
	 */
	private $name= '';

	/**
	 * Cookie value
	 * @var mixed
	 */
	private $value = '';

	/**
	 * Days after cookie expires
	 * @var int
	 */
	private $expire = 0;

	/**
	 * Path parameter
	 * @var string
	 */
	private $path = '/';

	/**
	 * Domain
	 * @var string
	 */
	private $domain = '';

	/**
	 * Secure flag
	 * @var boolean
	 */
	private $secure = false;

	/**
	 * Httponly flag
	 * @var boolean
	 */
	private $httponly = false;


	public function __construct()
	{

	}

	/**
	 * Returns cookie name
	 * @return the $name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns cookie value
	 * @return the $value
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Returns cookie expire time
	 * @return the $expire
	 */
	public function getExpire()
	{
		return $this->expire;
	}

	/**
	 * Returns cookie path
	 * @return the $path
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Returns cookie domain
	 * @return the $domain
	 */
	public function getDomain()
	{
		return $this->domain;
	}

	/**
	 * Returns cookie secure flag
	 * @return the $secure
	 */
	public function getSecure()
	{
		return $this->secure;
	}

	/**
	 * Returns cookie httponly flag
	 * @return the $httponly
	 */
	public function getHttponly()
	{
		return $this->httponly;
	}

	/**
	 * Sets name to be used for cookie
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Sets cookie value
	 * @param string $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}

	/**
	 * Set expire time
	 * @param int $expire
	 */
	public function setExpire($expire)
	{
		$this->expire = (int) $expire;
		return $this;
	}

	/**
	 * @param string $path
	 */
	public function setPath($path)
	{
		$this->path = $path;
		return $this;
	}

	/**
	 * Set domain
	 * @param string $domain
	 */
	public function setDomain($domain)
	{
		$this->domain = $domain;
		return $this;
	}

	/**
	 * Sets secure flag
	 * @param boolean $secure
	 */
	public function setSecure($secure)
	{
		$this->secure = $secure;
		return $this;
	}

	/**
	 * Sets httponly flag
	 * @param boolean $httponly
	 */
	public function setHttponly($httponly)
	{
		$this->httponly = $httponly;
		return $this;
	}

	/**
	 * Sets cookie by using given properties
	 * @return boolean
	 */
	public function set()
	{
		return setcookie(
			$this->name,
			$this->value,
			$this->expire,
			$this->path,
			$this->domain,
			$this->secure,
			$this->httponly
		);
	}

	/**
	 * Returns the requested cookie. Returns false if cookie is not set.
	 * @param string $name
	 * @return boolean|string
	 */
	public static function get($name)
	{
		return isset($_COOKIE[$name]) ? $_COOKIE[$name] : false;
	}

	/**
	 * Removes the named cookie on next page load
	 * @param string $name
	 */
	public static function remove($name)
	{
		if (isset($_COOKIE[$name]))
			setcookie($name, '', time()-3600);
	}

	/**
	 * Checks the named cookies exists
	 * @param string $name
	 * @return boolean
	 */
	public static function exists($name)
	{
		return isset($_COOKIE[$name]);
	}

	/**
	 * Checks cookie for emptiness
	 * @param string $name
	 * @return boolean
	 */
	public static function isEmpty($name)
	{
		return isset($_COOKIE[$name]) && empty($_COOKIE['name']);
	}
}

