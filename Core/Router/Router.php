<?php
namespace Core\Router;

// Commoln Traits
use Core\Traits\StringTrait;
use Core\Traits\ConvertTrait;

// Exceptions
use Core\Errors\Exceptions\InvalidArgumentException;

/**
 * Router.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
final class Router extends \AltoRouter
{
    use StringTrait;
    use ConvertTrait;

    /**
     * Status flag ajax
     *
     * @var bool
     */
    private $is_ajax = false;

    /**
     * Routered app
     *
     * @var string
     */
    private $app = 'Core';

    /**
     * Routered conroller
     *
     * @var string
     */
    private $controller = '';

    /**
     * Routeret action
     *
     * @var string
     */
    private $action = '';

    /**
     * Target parameter used in AJAX requestshandling
     *
     * @var string
     */
    private $target = '';

    /**
     * Storage for parameters
     *
     * @var array
     */
    private $params = [];

    /**
     * Default return format
     *
     * @var string
     */
    private $format = 'html';

    /**
     * Name of current route
     *
     * @var string
     */
    private $name = '';

    /**
     *
     * @var array
     */
    private $match = [];

    /**
     *
     * @var string
     */
    private $request_url = '';

    /**
     * Reversed routing for generating the URL for a named route
     *
     * @param string $route_name
     *            The name of the route.
     * @param array $params
     *            Associative array of parameters to replace placeholders with.
     *
     * @return string The URL of the route with named parameters in place.
     */
    public function url($route_name, $params = [])
    {
        array_walk_recursive($params, function (&$item, $key) {

            $aca = [
                'app',
                'controller',
                'action'
            ];

            if (in_array($key, $aca)) {
                $item = $this->stringUncamelize($item);
            }
        });

        return $this->generate($route_name, $params);
    }

    public function mapAppRoutes($app_name, array $routes)
    {
        // Get uncamelized app name
        $app = $this->stringUncamelize($app_name);

        // Add always a missing index route!
        if (! array_key_exists('index', $routes)) {
            $routes['index'] = [];
        }

        foreach ($routes as $name => $route) {

            if (empty($name)) {
                Throw new RouterException(sprintf('App "%s" sent a nameles route to be mapped.', $app_name));
            }

            $name = $this->stringUncamelize($name);

            if (empty($route['route']) || empty($route['target'])) {

                // Try to get controller and action from route name
                $ca = explode('.', $name);

                if (empty($route['route'])) {
                    $route['route'] = '/' . $ca[0];
                }

                if (empty($route['target'])) {
                    $route['target'] = [
                        'controller' => empty($ca[0]) ? $name : $ca[0],
                        'action' => empty($ca[1]) ? $name : $ca[1]
                    ];
                }
            }

            // Create route string
            if ($route['route'] == '/') {
                $route['route'] = '/' . $app;
            }
            else {
                if (strpos($route['route'], '../') === false && $app != 'generic') {
                    $route['route'] = '/' . $app . $route['route'];
                }
                else {
                    $route['route'] = str_replace('../', '', $route['route']);
                }
            }

            if (empty($route['target']['app']) && $app != 'generic') {
                $route['target']['app'] = $app;
            }

            if (empty($route['method'])) {
                $route['method'] = 'GET';
            }

            if (strpos($name, $app) === false) {
                $name = $app . '.' . $name;
            }

            $this->map($route['method'], $route['route'], $route['target'], $name);
        }
    }

    /**
     * Match a given request url against stored routes
     *
     * @param string $request_url
     * @param string $request_method
     *
     * @return array boolean with route information on success, false on failure (no match).
     */
    public function match($request_url = null, $request_method = null)
    {

        // Set Request Url if it isn't passed as parameter
        if ($request_url === null) {
            $request_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        }

        $this->request_url = $request_url;

        // Set Request Method if it isn't passed as a parameter
        if ($request_method === null) {
            $request_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        }

        // Framework.js adds automatically an /ajax flag @ the end of the requested URI.
        // Here we check for this flag, remembers if it's present and then remove the flag
        // so the following URI matching process runs without flaw.
        if (substr($request_url, - 5) == '/ajax') {
            $this->is_ajax = true;
            $request_url = str_replace('/ajax', '', $request_url);
        }

        $this->match = parent::match($request_url, $request_method);

        if (! empty($this->match)) {

            if (isset($this->match['name'])) {
                $this->name = $this->match['name'];
            }

            // Map target results to request properties
            foreach ($this->match['target'] as $key => $val) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $this->stringCamelize($val);
                }
            }

            // Some parameters will always override target settings from route
            $overrides = [
                'app',
                'controller',
                'action'
            ];

            foreach ($overrides as $key) {
                if (! empty($this->match['params'][$key])) {
                    $this->{$key} = $this->stringCamelize($this->match['params'][$key]);
                }
            }

            // Some parameters only have control or workflow character and are no parameters for public use.
            // Those will be removed from the parameters array after using them to set corresponding values and/or flags
            // in router.
            $controls = [
                'ajax',
                'format'
            ];

            foreach ($controls as $key) {

                switch (true) {
                    case $key == 'ajax' && isset($this->match['params'][$key]):
                        $this->is_ajax = true;
                        break;

                    default:
                        if (isset($this->match['params'][$key])) {
                            $this->{$key} = $this->match['params'][$key];
                        }
                        break;
                }

                unset($this->match['params'][$key]);
            }

            $this->params = $this->match['params'];
        }

        \FB::log($this->getStatus());

        return $this->match;
    }

    /**
     * Returns the name of the current active route.
     *
     * @return string
     */
    public function getCurrentRoute()
    {
        return $this->name;
    }

    /**
     * Checks for an ajax request and returns boolean true or false
     *
     * @return boolean
     */
    public function isAjax()
    {
        return $this->is_ajax;
    }

    /**
     * Checks if the request is a (A)pp(C)ontroller(A)ction call
     *
     * @return boolean
     */
    public function isCall()
    {
        return $this->checkApp() && $this->checkController() && $this->checkAction();
    }

    /**
     * Check set of app property.
     *
     * @return boolean
     */
    public function checkApp()
    {
        return isset($this->app);
    }

    /**
     * Returns a camelized app name
     *
     * @return string
     */
    public function getApp()
    {
        return $this->app ? $this->app : false;
    }

    /**
     * Set appname manually
     *
     * @param string $val
     *
     * @return \Core\Http\Router
     */
    public function setApp($app)
    {
        $this->app = $app;

        return $this;
    }

    /**
     * Checks if a controller is set and returns true/false
     *
     * @return boolean
     */
    public function checkController()
    {
        return ! empty($this->controller);
    }

    /**
     * Returns a camelized controller name
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Sets the requested controller manually
     *
     * @param string $controller
     *
     * @return \Core\Http\Router
     */
    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * Sets requested output format
     *
     * @param string $format
     *            Output format: xml, json or html
     *
     * @throws InvalidArgumentException
     *
     * @return \Core\Http\Router
     */
    public function setFormat($format)
    {
        $allowed = [
            'html',
            'xml',
            'json',
            'file'
        ];

        if (! in_array(strtolower($format), $allowed)) {
            Throw new RouterException(sprintf('Your format "%s" is not an allowed format. Use one of these formats %s', $format, implode(', ', $allowed)));
        }

        $this->format = $format;

        return $this;
    }

    /**
     * Checks if the function name is set
     *
     * @return boolean
     */
    public function checkAction()
    {
        return ! empty($this->action);
    }

    /**
     * Returns either the requested or 'Index' (default) as action name
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the requested func manually
     *
     * @param string $action
     *
     * @return \Core\Router\Router
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Returns requested app, controller and action as array
     *
     * @return array
     */
    public function getACA()
    {
        return [
            'app' => $this->app,
            'controller' => $this->controller,
            'action' => $this->action
        ];
    }

    /**
     * Boolean check if parameter exists
     *
     * @param mixed $key
     *            The key to look for in router params
     *
     * @return boolean
     */
    public function checkParam($key)
    {
        return array_key_exists($key, $this->params);
    }

    /**
     * Sets one or mor request parameter.
     * Set $arg1 as assoc array to add multiple parameter.
     * Both args ($arg1 and $arg2) set means to add a parameter by key ($arg1) and value ($arg2)
     *
     * @param string|array $arg1
     *
     * @param string $arg2
     *
     * @return \Core\Router
     */
    public function addParam($arg1, $arg2 = null)
    {
        if ($arg2 === null && is_array($arg1)) {

            $arg1 = $this->convertObjectToArray($arg1);

            foreach ($arg1 as $key => $val) {
                $this->params[$key] = $val;
            }
        }

        if ($arg2 !== null) {
            $this->params[$arg1] = $this->convertObjectToArray($arg2);
        }

        return $this;
    }

    /**
     * Returns the value of a paramter as long as it exists.
     *
     * @param string $key
     */
    public function getParam($key = null)
    {
        if (isset($key)) {
            return isset($this->params[$key]) ? $this->params[$key] : false;
        }

        return $this->params;
    }

    /**
     * Sets one or more router parameter.
     *
     * @param string $param
     *
     * @param mnixed $value
     *
     * @throws InvalidArgumentException
     *
     * @return \Core\Router\Router
     */
    public function setParam($param, $value = null)
    {
        if (! is_array($param) && $value === null) {
            Throw new RouterException('Setting router parameter with NULL value is not allowed');
        }

        if (! is_array($param)) {
            $param = [
                $param => $value
            ];
        }

        foreach ($param as $key => $value) {
            $this->params[$key] = $value;
        }

        return $this;
    }

    /**
     * Returns the requested outputformat.
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Returns all request related data in one array
     *
     * @return array
     */
    public function getStatus()
    {
        return [
            'url' => $this->request_url,
            'route' => $this->getCurrentRoute(),
            'is_ajax' => $this->is_ajax,
            'method' => $_SERVER['REQUEST_METHOD'],
            'app' => $this->getApp(),
            'controller' => $this->getController(),
            'action' => $this->getAction(),
            'params' => $this->getParam(),
            'format' => $this->getFormat(),
            'match' => $this->match
        ];
    }

    /**
     * Returns mapped routes stack
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}
