<?php
namespace Core\Cfg;

use Core\Storage\Storage;

/**
 * AppCfg.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class AppCfg extends Storage
{

    public function getValue($key)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        Throw new CfgException(sprintf('Config "%s" does not exists.', $key));
    }
}

