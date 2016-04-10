<?php
namespace Core\Http\Cookie;

/**
 * Cookie.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Cookie
{

    /**
     * Cookie name
     *
     * @var string
     */
    private $name = '';

    /**
     * Cookie value
     *
     * @var mixed
     */
    private $value = '';

    /**
     * Days after cookie expires
     *
     * @var int
     */
    private $expire = 0;

    /**
     * Path parameter
     *
     * @var string
     */
    private $path = '/';

    /**
     * Domain
     *
     * @var string
     */
    private $domain = '';

    /**
     * Secure flag
     *
     * @var boolean
     */
    private $secure = false;

    /**
     * Httponly flag
     *
     * @var boolean
     */
    private $httponly = false;

    /**
     * Creates new instance.
     *
     * @return Cookie
     */
    public function getInstance()
    {
        return new self();
    }

    /**
     * Returns cookie name.
     *
     * @return the $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns cookie value.
     *
     * @return the $value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns cookie expire time.
     *
     * @return the $expire
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * Returns cookie path.
     *
     * @return the $path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns cookie domain.
     *
     * @return the $domain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Returns cookie secure flag.
     *
     * @return the $secure
     */
    public function getSecure()
    {
        return $this->secure;
    }

    /**
     * Returns cookie httponly flag.
     *
     * @return the $httponly
     */
    public function getHttponly()
    {
        return $this->httponly;
    }

    /**
     * Sets name to be used for cookie.
     *
     * @param string $name
     *
     * @return \Core\Http\Cookie
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Sets cookie value.
     *
     * @param string $value
     *
     * @return \Core\Http\Cookie
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Set expire time.
     *
     * @param int $expire
     *
     * @return \Core\Http\Cookie
     */
    public function setExpire($expire)
    {
        $this->expire = (int) $expire;

        return $this;
    }

    /**
     * Sets cookie path
     *
     * @param string $path
     *
     * @return \Core\Http\Cookie
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Set domain.
     *
     * @param string $domain
     *
     * @return \Core\Http\Cookie
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Sets secure flag.
     *
     * @param boolean $secure
     *
     * @return \Core\Http\Cookie
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;

        return $this;
    }

    /**
     * Sets httponly flag.
     *
     * @param boolean $httponly
     *
     * @return \Core\Http\Cookie
     */
    public function setHttponly($httponly)
    {
        $this->httponly = $httponly;

        return $this;
    }
}
