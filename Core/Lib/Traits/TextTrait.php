<?php
namespace Core\Lib\Traits;

use Core\Lib\Amvc\App;
use Core\Lib\Errors\Exceptions\RuntimeException;

/**
 * TextTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
trait TextTrait
{

    /**
     * Get text from language service.
     *
     * @param string $key
     * @param string $app
     *
     * @throws RuntimeException
     *
     * @return string
     */
    public function txt($key, $app = '')
    {
        if (!property_exists($this, 'di')) {
            Throw new RuntimeException('TextTrait::txt() method cannot work without access to DI service container. Make sure that the object using this trait has this property set.');
        }

        if (empty($app)) {

            if (! property_exists($this, 'app_name')) {

                if ($this instanceof App) {
                    $app = $this->name;
                }
                elseif (property_exists($this, 'app')) {
                    $app = $this->app->getName();
                }
                else {
                    $app = 'Core';
                }
            }
            else {
                $app = $this->app_name;
            }
        }

        return $this->di->get('core.content.lang')->getTxt($key, $app);
    }
}
