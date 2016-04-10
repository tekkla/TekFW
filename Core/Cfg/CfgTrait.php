<?php
namespace Core\Cfg;

use Core\Errors\Exceptions\InvalidArgumentException;
use Core\Amvc\App;
use Core\Cfg\Cfg;

/**
 * CfgTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
trait CfgTrait {

    /**
     * Gives access on the config service
     *
     * Calling only with key returns the set value.
     * Calling with key and value will set the apps config.
     * Calling without any parameter will return complete app config
     *
     * @param string $key
     * @param string $val
     *
     * @return void boolean \Core\Cfg
     *
     * @throws InvalidArgumentException
     */
    public function cfg($key = null, $val = null, $app = '')
    {
        static $cfg;

        if (empty($app)) {

            if (! property_exists($this, 'app_name')) {

                if ($this instanceof App) {
                    $app = $this->name;
                } elseif (property_exists($this, 'app')) {
                    $app = $this->app->getName();
                } else {
                    $app = 'Core';
                }
            } else {
                $app = $this->app_name;
            }
        }

        if (!$cfg instanceof Cfg) {
            /* @var $cfg \Core\Cfg\Cfg */
            $cfg = \Core\DI::getInstance()->get('core.cfg');
        }

        // Getting config
        if (isset($key) && ! isset($val)) {
            return isset($cfg->data[$app][$key]) ? $cfg->data[$app][$key] : $key;
        }

        // Setting config
        if (isset($key) && isset($val)) {
            $cfg->data[$app][$key] = $val;
            return;
        }

        // Return complete config
        if (! isset($key) && ! isset($val)) {
            return $cfg->data[$app];
        }

        Throw new InvalidArgumentException('Values without keys cannot be used to access config service.');
    }
}
