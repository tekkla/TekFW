<?php
namespace Core\Config;

/**
 * ConfigInterface.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
interface ConfigInterface
{

    /**
     * Returns a config value from a storage
     *
     * @param string $storage_name
     *            The name of the storage to query
     * @param string $key
     *            The key we are looking for in the storage
     *
     * @return string
     */
    public function get($storage_name, $key);
}

