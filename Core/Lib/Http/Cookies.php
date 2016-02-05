<?php
namespace Core\Lib\Http;

/**
 * Cookies.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Cookies
{

    private $cookies = [];

    /**
     * Sets cookie by using given properties
     *
     * @return boolean
     */
    public function set()
    {
        /* @var $cookie \Core\Lib\Http\Cookie */
        foreach ($this->cookies as $cookie) {
            setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpire(), $cookie->getPath(), $cookie->getDomain(), $cookie->getSecure(), $cookie->getHttponly());
        }
    }

    public function &createCookie()
    {
        $cookie = new Cookie();

        $this->cookies[] = $cookie;

        return $cookie;
    }

    public function addCookie(Cookie $cookie)
    {
        $this->cookies[] = $cookie;

        return $this;
    }

    /**
     * Returns the requested cookie.
     * Returns false if cookie is not set.
     *
     * @param string $name
     * @return boolean|string
     */
    public static function get($name)
    {
        if (isset($_COOKIE[$name])) {} else {
            return false;
        }

        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : false;
    }

    /**
     * Removes the named cookie on next page load
     *
     * @param string $name
     */
    public static function remove($name)
    {
        if (isset($_COOKIE[$name])) {
            setcookie($name, '', time() - 3600);
        }
    }

    /**
     * Checks the named cookies exists
     *
     * @param string $name
     *
     * @return boolean
     */
    public static function exists($name)
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * Checks cookie for emptiness
     *
     * @param string $name
     *
     * @return boolean
     */
    public static function isEmpty($name)
    {
        return isset($_COOKIE[$name]) && empty($_COOKIE['name']);
    }
}
