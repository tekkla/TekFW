<?php
namespace Core\Lib\Cache;

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

        $fp = fopen($filename, 'w+');

        if ($object->getExtension() == 'php') {

            // Important to set the filemodification as objects timestamp!
            $object->setTimestamp(filemtime($filename));

            $data = $object->export();

        } else {
           $data = $object->getContent();
        }

        $fw = fwrite($fp, $data);

        fclose($fp);
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
            $object->import(include ($filename));
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
}

