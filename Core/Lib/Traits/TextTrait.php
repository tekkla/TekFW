<?php
namespace Core\Lib\Traits;

use Core\Lib\Amvc\App;

/**
 * Text Trait
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
     * @return string
     */
    public function txt($key, $app = '')
    {
        global $di;

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

        return $di->get('core.content.lang')->getTxt($key, $app);
    }
}
