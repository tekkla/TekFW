<?php
namespace Core\Config;

/**
 * Cfg.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Cfg
{

    /**
     *
     * @var string
     */
    private $storage_name;

    /**
     *
     * @var ConfigInterface
     */
    private $config;

    /**
     *
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Sets the name of the storage to query
     *
     * @param string $storage_name
     */
    public function setStorageName($storage_name)
    {
        $this->storage_name = $storage_name;
    }

    /**
     * Queries storage for a value mapped to a key.
     *
     * @param string $key
     */
    public function get($key)
    {
        if (! isset($this->config)) {
            Throw new ConfigException('Cfg neeeds a set Config object.');
        }

        if (! isset($this->storage_name)) {
            Throw new ConfigException('No storage name set.');
        }

        return $this->config->get($this->storage_name, $key);
    }
}
