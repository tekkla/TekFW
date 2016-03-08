<?php
namespace Core\Lib\Http\Cookie;

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
     * Send all cookies by using given properties
     *
     * @return boolean
     */
    public function send()
    {
         /* @var $cookie \Core\Lib\Http\Cookie */
        foreach ($this->cookies as $cookie) {
            setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpire(), $cookie->getPath(), $cookie->getDomain(), $cookie->getSecure(), $cookie->getHttponly());
        }
    }

    /**
     *
     * @return \Core\Lib\Http\Cookie\Cookie
     */
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
     * Returns the requested cookie
     *
     * Returns false if cookie is not set.
     *
     * @param string $name
     *
     * @return boolean|string
     */
    public function get($name)
    {
        if (isset($_COOKIE[$name])) {}
        else {
            return false;
        }

        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : false;
    }

    /**
     * Removes the named cookie on next page load
     *
     * @param string $name
     *            Cookiename
     */
    public function remove($name)
    {
        $cookie = $this->createCookie();
        $cookie->setName($name);
        $cookie->setValue('');
        $cookie->setExpire(time() - 3600);
    }

    /**
     * Checks the named cookies exists
     *
     * @param string $name
     *
     * @return boolean
     */
    public function exists($name)
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
    public function isEmpty($name)
    {
        return isset($_COOKIE[$name]) && empty($_COOKIE['name']);
    }
}
