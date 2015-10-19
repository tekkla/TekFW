<?php
namespace Core\Lib\Traits;

use Core\Lib\Amvc\App;
use Core\Lib\Errors\Exceptions\RuntimeException;

/**
 * UrlTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
trait UrlTrait
{

    use StringTrait;

    /**
     * Generates url by using routename and optional prameters.
     *
     * @param string $route Name of route to compile
     * @param array $params Optional parameter list
     *
     * @throws RuntimeException
     *
     * @return string
     */
    protected function url($route, Array $params = [], $app = '')
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
