<?php
namespace Core\Lib\IO;

/**
 * CacheObject.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class CacheObject
{

    private $key;

    private $data;

    private $extension = 'php';

    private $ttl = 3600;

    private $cachedir = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        if (defined('CACHEDIR')) {
            $this->cachedir = CACHEDIR;
        }
    }

    /**
     *
     * @return the $key
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     *
     * @return the $data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     *
     * @return the $extension
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     *
     * @return the $ttl
     */
    public function getTTL()
    {
        return $this->ttl;
    }

    /**
     *
     * @return the $cachedir
     */
    public function getCachdir()
    {
        return $this->cachedir;
    }

    /**
     * Returns the full path of cacheobject.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->cachedir . '/' . $this->key . '.' . $this->extension;
    }

    /**
     * Sets key (filename) to use for cachfile.
     *
     * @param string $key
     *
     * @return CacheObject
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Sets the data do cache.
     *
     * @param string $data
     *
     * @return CacheObject
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Sets (file)extension to use for cachefile.
     *
     * @param string $extension
     *
     * @return CacheObject
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Sets TTL (in seconds) of cacheobject.
     *
     * @param number $ttl
     *
     * @return CacheObject
     */
    public function setTTL($ttl)
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * Sets TTL (in seconds) of cacheobject.
     *
     * @param number $ttl
     *
     * @return CacheObject
     */
    public function setCachdir($cachedir)
    {
        $this->cachedir = $cachedir;

        return $this;
    }
}
