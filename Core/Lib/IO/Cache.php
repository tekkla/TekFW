<?php
namespace Core\Lib\IO;

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
     * Creates and returns a new CacheObject.
     *
     * @return \Core\Lib\IO\CacheObject
     */
    public function createCacheObject()
    {
        return $this->di->get('core.io.cache.object');
    }

    /**
     * Creates cachefile.
     *
     * @param CacheObject $object
     */
    public function put(CacheObject $object)
    {
        $filename = $object->getFilename();

        $fp = fopen($filename, 'w+');

        $object->setTimestamp(filemtime($filename));

        $data = $object->getExtension() == 'php' ? $object->export() : $object->getContent();

        $fw = fwrite($fp, $data);

        fclose($fp);
    }

    /**
     * Fills cacheobject with data. Data
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
            $object->import(include($filename));
        }
        else {
            $object->setContent(file_get_contents($filename));
        }

        return true;
    }

    /**
     * Checks a CacheObject to be expired. Returns boolean true or false.
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

        // Check
        if (filemtime($filename) + $object->getTTL() < time()) {

            // Remove expired file!
            unlink($filename);

            return true;
        }

        return false;
    }
}

