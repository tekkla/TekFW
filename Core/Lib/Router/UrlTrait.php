<?php
namespace Core\Lib\Router;

// AMVC Libs
use Core\Lib\Amvc\App;

// Common Traits
use Core\Lib\Traits\StringTrait;

// Exceptions
use Core\Lib\Errors\Exceptions\RuntimeException;

/**
 * UrlTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
trait UrlTrait
{

    use StringTrait;

    /**
     * Generates url by using routename and optional prameters
     *
     * @param string $route
     *            Name of route to compile
     * @param array $params
     *            Optional parameter list
     *
     * @throws RuntimeException
     *
     * @return string
     */
    protected function url($route, Array $params = [], $app = '', $append_baseurl = true)
    {
        static $router;

        if (empty($app)) {

            if (! property_exists($this, 'app_name')) {

                if ($this instanceof App) {
                    $app = $this->name;
                } elseif (property_exists($this, 'app')) {
                    $app = $this->app->getName();
                } else {
                    $app = 'core';
                }
            } else {
                $app = $this->app_name;
            }
        }

        $app = $this->stringUncamelize($app);

        if (strpos($route, $app) === false) {
            $route = $app . '_' . $route;
        }

        if (! $router instanceof Router) {
            $router = \Core\Lib\Di::getInstance()->get('core.router');
        }

        $url = $router->url($route, $params);

        if ($append_baseurl) {
            $url = BASEURL . $url;
        }

        return $url;
    }
}
