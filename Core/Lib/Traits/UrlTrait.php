<?php
namespace Core\Lib\Traits;

use Core\Lib\Amvc\App;

/**
 * Url Trait
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2015
 */
trait UrlTrait
{

    use StringTrait;

    /**
     * Generates url by using routename and optional prameters
     *
     * @param string $route Name of route to compile
     * @param array $params Optional parameter list
     */
    protected function url($route, Array $params = [], $app = '')
    {
        if (empty($app)) {

            if (! property_exists($this, 'app_name')) {

                if ($this instanceof App) {
                    $app = $this->name;
                }
                elseif (property_exists($this, 'app')) {
                    $app = $this->app->getName();
                }
                else {
                    $app = 'core';
                }
            }
            else {
                $app = $this->app_name;
            }
        }

        $app = $this->uncamelizeString($app);

        if (strpos($route, $app) === false) {
            $route = $app . '_' . $route;
        }

        return $this->di->get('core.http.router')->url($route, $params);
    }
}
