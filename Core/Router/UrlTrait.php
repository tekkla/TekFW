<?php
namespace Core\Router;

use Core\Amvc\App;

/**
 * UrlTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
trait UrlTrait
{

    /**
     * Generates url
     *
     * @param string $route
     *            Name of route to compile.
     * @param array $params
     *            Optional key/value base parameter array.
     * @param string $app
     *            Optional appnamestring this routes is from. Will be autodiscorvered when not provided.
     * @param boolean $prend_baseurl
     *            Optional flag to prepend BASEURL value in front of the compiled route.
     *
     * @return string The compiled route
     */
    protected function url($route, array $params = [], $app = '', $prepend_baseurl = true)
    {
        // We need a reference to the Router service
        $router = $this->di->get('core.router');

        // When app arguement is not provided, we try to identify which apps route is called
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

        // Make appstring lowercase
        if (function_exists('\Core\stringUncamelize')) {
            $app = \Core\stringUncamelize($app);
        }

        // Use appsstring as parameter when there is no appstring present
        if (empty($params['app'])) {
            $params['app'] = $app;
        }

        // Take care that the appstring ist prepended to the routes name while a generic name is ignored
        if (strpos($route, $app) === false && strpos($route, 'generic.') === false) {
            $route = $app . '.' . $route;
        }

        // Get the compiled route from the router...
        $url = $router->url($route, $params);

        // ... and prepend the sites baseurl on demand
        if (! empty($prepend_baseurl)) {
            $url = BASEURL . $url;
        }

        return $url;
    }

    /**
     * Creates an url for id based actions like edits or deletes or detailpages
     *
     * @param string $controller
     *            Name of the controller to be compiled into route
     * @param string $action
     *            Name of the action to be compiled into route
     * @param integer $id
     *            The id to be compiled into route
     * @param string $app
     *            Optional appname string. Will be tried to autodiscover when not provided.
     *
     * @return string The compiled route
     */
    protected function urlId($controller, $action, $id, $app = '')
    {
        $route = 'generic.id';
        $params = [
            'controller' => $controller,
            'action' => $action,
            'id' => $id
        ];

        return $this->url($route, $params, $app);
    }

    /**
     * Creates an url for parent and id based actions for parent/child relations
     *
     * @param string $controller
     *            Name of the controller to be compiled into route
     * @param string $action
     *            Name of the action to be compiled into route
     * @param integer $id_parent
     *            The id of the parent to be compiled into route
     * @param integer $id
     *            Optional id to be compiled into route
     * @param string $app
     *            Optional appname string. Will be tried to autodiscover when not provided.
     *
     * @return string The compiled route
     */
    protected function urlChild($controller, $action, $id_parent, $id = '', $app = '')
    {
        $route = 'generic.child';
        $params = [
            'controller' => $controller,
            'action' => $action,
            'id_parent' => $id_parent
        ];

        if (isset($id) && $id != '') {
            $params['id'] = $id;
        }

        return $this->url($route, $params, $app);
    }

    /**
     * Creates an url for Controller::Index() calls
     *
     * @param string $controller
     *            Name of the controller to be compiled into route
     * @param string $app
     *            Optional appname string. Will be tried to autodiscover when not provided
     *
     * @return string The compiled route
     */
    protected function urlIndex($controller, $app = '')
    {
        $route = 'generic.index';
        $params = [
            'controller' => $controller
        ];

        return $this->url($route, $params, $app);
    }

    /**
     * Creates an url for Controller::YourAction() calls
     *
     * @param string $controller
     *            Name of the controller to be compiled into route
     * @param string $action
     *            Name of the action to be compiled into route
     * @param string $app
     *            Optional appname string. Will be tried to autodiscover when not provided
     *
     * @return string The compiled route
     */
    protected function urlAction($controller, $action, $app = '')
    {
        $route = 'generic.action';
        $params = [
            'controller' => $controller,
            'action' => $action
        ];

        return $this->url($route, $params, $app);
    }
}
