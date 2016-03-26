<?php
namespace Core\Lib\Language;

use Core\Lib\Amvc\App;
use Core\Lib\Errors\Exceptions\RuntimeException;

/**
 * TextTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 *
 * @uses \Core\Lib\DI
 */
trait TextTrait
{

    /**
     * Returns translated text
     *
     * Only translates texts that does not contain spaces.
     * Tries to find core text when either app language or the requested key is found.
     * Returns the key when no language text is found.
     * Allows linking of keys and throws an exception when it comes to ininite loops.
     *
     * @param string $key
     *            Key of the requested text
     * @param string $app
     *            Optional app name the key belongs to. (Default: 'Core')
     *
     * @throws RuntimeException
     *
     * @return string
     */
    public function text($key, $app = '')
    {
        static $lang;

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

        if (! $lang instanceof Language) {
            $lang = \Core\Lib\DI::getInstance()->get('core.language');
        }

        return $lang->getText($key, $app);
    }
}
