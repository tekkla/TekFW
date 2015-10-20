<?php
namespace Core\Lib\Cache;

use Core\Lib\Cfg;
use Core\Lib\Errors\Exceptions\RuntimeException;

/**
 * Cache.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Cache
{

    /**
     *
     * @var Cfg
     */
    private $cfg;

    /**
     *
     * @var \Memcache
     */
    private $memcache = false;


    /**
     *
     * @param Cfg $cfg
    */
    public function __construct(Cfg $cfg)
    {
        $this->cfg = $cfg;

        // Connect to memcache server?
        $this->connectMemcached();
    }

    /**
     * Creates and returns a new CacheObject.
     *
     * @return \Core\Lib\Cache\CacheObject
     */
    public function createCacheObject()
    {
        return $this->di->get('core.cache.object');
    }

    /**
     * Creates cachefile.
     *
     * @param CacheObject $object
     */
    public function put(CacheObject $object)
    {
        $filename = $object->getFilename();

        if ($object->getExtension() == 'php') {

            if ($this->memcache) {
                $object->setTimestamp();
                $data = $object->export();
            }
            else {
                $data = $object->export(true);
            }
        }
        else {
            $fp = fopen($filename, 'w+');
            // Important to set the filemodification as objects timestamp!
            $object->setTimestamp(filemtime($filename));

            $data = $object->getContent();
        }

        if ($this->memcache) {
            $this->memcache->put($filename, $data, $object->getExpiresOn());
        }
        else {
            $fw = fwrite($fp, $data);
            fclose($fp);
        }
    }

    /**
     * Fills cacheobject with data.
     * Data
     *
     * @param string $key
     *
     * @return boolean
     */
    public function get(CacheObject $object)
    {
        // Check for expired
        if ($this->checkExpired($object)) {
            return false;
        }

        $filename = $object->getFilename();

        if ($object->getExtension() == 'php') {

            $content = $this->memcache ? $this->memcache->get($filename) : include ($filename);

            $object->import($content);
        }
        else {
            $object->setContent(file_get_contents($filename));
        }

        return true;
    }

    /**
     * Checks a CacheObject to be expired.
     *
     * @param CacheObject $object
     *
     * @return boolean
     */
    public function checkExpired(CacheObject $object)
    {
        $filename = $object->getFilename();

        if (! file_exists($filename)) {
            return true;
        }

        // While data based cache objects have stored their creation timestamp within
        // the data itself, file based objects need to use the time of the filecreation.
        if ($object->getExtension() == 'php') {
            $object->import(include ($filename));
        }
        else {
            $object->setTimestamp(filemtime($filename));
        }

        return $object->checkExpired();
    }

    /**
     * Tries to connect to memcache server set in config.
     *
     * @throws RuntimeException
     */
    private function connectMemcached()
    {
        return true;

        if ($this->cfg->exists('Core', 'cache_memcache_server') && class_exists('\Memcache')) {

            $host = $this->cfg->get('Core', 'cache_memcache_server');
            $port = $this->cfg->get('Core', 'cache_memcache_port');

            $this->memcache = new \Memcache();

            $connected = $this->memcache->connect($host, $port);

            if (!$connected){
                Throw new RuntimeException('Unable to connect to memcache server');
            }
        }
    }
}

