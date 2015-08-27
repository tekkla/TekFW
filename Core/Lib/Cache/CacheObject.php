<?php
namespace Core\Lib\Cache;

/**
 * CacheObject.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class CacheObject
{

    private $data = [
        'key' => '',
        'content' => '',
        'ttl' => 3600,
        'timestamp' => 0,
        'cachedir' => ''
    ];

    private $expires_on = 0;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (defined('CACHEDIR')) {
            $this->data['cachedir'] = CACHEDIR;
        }

        $this->data['timestamp'] = time();

        $this->expires_on = $this->data['timestamp'] + $this->data['ttl'];
    }

    /**
     *
     * @return the $key
     */
    public function getKey()
    {
        return $this->data['key'];
    }

    /**
     *
     * @return the $content
     */
    public function getContent()
    {
        return base64_decode($this->data['content']);
    }

    /**
     *
     * @return the $extension
     */
    public function getExtension()
    {
        return $this->data['extension'];
    }

    /**
     *
     * @return the $ttl
     */
    public function getTTL()
    {
        return $this->data['ttl'];
    }

    /**
     *
     * @return the $ttl
     */
    public function getTimestamp()
    {
        return $this->data['timestamp'];
    }

    public function getExpiresOn()
    {
        return $this->expires_on;
    }

    /**
     *
     * @return the $cachedir
     */
    public function getCachdir()
    {
        return $this->data['cachedir'];
    }

    /**
     * Returns the full path of cacheobject.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->data['cachedir'] . '/' . $this->data['key'] . '.' . $this->data['extension'];
    }

    /**
     * Exoired status of caceobject
     *
     * @return boolean
     */
    public function checkExpired()
    {
        if ($this->expires_on < time()) {
            $this->expired();
            return true;
        }

        return false;
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
        $this->data['key'] = $key;

        return $this;
    }

    /**
     * Sets the content to cache.
     *
     * @param string $content
     *
     * @return CacheObject
     */
    public function setContent($content)
    {
        $this->data['content'] = base64_encode($content);

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
        $this->data['extension'] = $extension;

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
        $this->data['ttl'] = $ttl;

        // Update internal expiring timestamp
        $this->expires_on = $ttl + $this->data['timestamp'];

        return $this;
    }

    /**
     * Sets timestamp of cacheobject creation.
     *
     * @param number $timestamp
     *
     * @return CacheObject
     */
    public function setTimestamp($timestamp = null)
    {
        if (! $timestamp) {
            $timestamp = time();
        }

        $this->data['timestamp'] = $timestamp;

        // Update internal expiring timestamp
        $this->expires_on = $timestamp + $this->data['ttl'];

        return $this;
    }

    /**
     * Sets expires on timestamp
     *
     * @param number $timestamp
     *
     * @return CacheObject
     */
    public function setExpiresOn($timestamp)
    {
        $this->expires_on = $timestamp;

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
        $this->data['cachedir'] = $cachedir;

        return $this;
    }

    /**
     * Returns the cache objects data array in a var_export() like way.
     *
     * @return string
     */
    public function export()
    {
        return '<?php return ' . var_export($this->data, true) . '; ?>';
    }

    /**
     * Imports an array as cache object data.
     *
     * @param array $data
     *
     * @return CacheObject
     */
    public function import(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Sets timestamp and TTL of object to zero, content to an boolean false
     * and removes the cache file from cache storage.
     */
    public function expired()
    {
        $this->data['timestamp'] = 0;
        $this->data['ttl'] = 0;
        $this->data['content'] = false;

        // Set internal expiring timestamp to 0
        $this->expires_on = 0;

        unlink($this->getFilename());
    }
}
