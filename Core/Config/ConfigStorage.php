<?php
namespace Core\Config;

use Core\Storage\Storage;

/**
 * ConfigStorage.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class ConfigStorage extends Storage
{

    public function getValue($key)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        Throw new ConfigException(sprintf('Config "%s" does not exists.', $key));
    }
}

