<?php
namespace Core\Lib\Traits;

use Core\Lib\Amvc\App;
use Core\Lib\Errors\Exceptions\RuntimeException;
use Core\Lib\Content\Language;

/**
 * TextTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 *
 * @uses \Core\Lib\DI
 */
trait TextTrait
{

    /**
     * Gets text from language service
     *
     * @param string $key
     *            Name of the key to receive content of
     * @param string $app
     *            Optional app name. When not provided, the method tries to identify the app from which component it was
     *            called and uses this name. When even this fails, the method falls back to the 'Core' app name.
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

        if (!$lang instanceof Language) {
            $lang = \Core\Lib\DI::getInstance()->get('core.content.lang');
        }

        return $lang->getText($key, $app);
    }
}
