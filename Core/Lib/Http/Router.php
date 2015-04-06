<?php
namespace Core\Lib\Http;

use Core\Lib\Traits\StringTrait;
use Core\Lib\Traits\ConvertTrait;

/**
 * Router class which handles routes and request like post or get.
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
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
    private $app = '';

    /**
     * Routered conroller
     *
     * @var string
     */
    private $ctrl = '';

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



    private $raw = false;

    /**
     * Reversed routing
     * Generate the URL for a named route.
     * Replace regexes with supplied parameters
     *
     * @param string $route_name The name of the route.
     * @param array @params Associative array of parameters to replace placeholders with.
     * @return string The URL of the route with named parameters in place.
     * @todo Add access check
     */
    public function url($route_name, $params = [])
    {
        return $this->generate($route_name, $params);
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

        $this->raw = parent::match($request_url, $request_method);

        // Try to match request
        $match = $this->raw;

        if ($match) {

            if (isset($match['name'])) {
                $this->name = $match['name'];
            }

            // Map target results to request properties
            foreach ($match['target'] as $key => $val) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $this->camelizeString($val);
                }
            }

            // When no target ctrl defined in route but provided by parameter
            // we use the parameter as requested ctrl
            if (! $this->ctrl && isset($match['params']['ctrl'])) {
                $this->ctrl = $this->camelizeString($match['params']['ctrl']);
            }

            // Same for action as for ctrl
            if (! $this->action && isset($match['params']['action'])) {
                $this->action = $this->camelizeString($match['params']['action']);
            }

            // Is this an ajax request?
            // @TODO Still needed?
            if (isset($match['params']['ajax'])) {
                $this->is_ajax = true;
            }

            // Handle format parameter seperately and remove it from match params array.
            if (isset($match['params']['format'])) {
                $this->format = $match['params']['format'];
                unset($match['params']['format']);
            }

            // Stroe params
            $this->params = $match['params'];

            #var_dump($this);
        }

        return $match;
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
        return $this->checkApp() && $this->checkCtrl() && $this->checkAction();
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
     * @return \Core\Lib\Http\Router
     */
    public function setApp($app)
    {
        $this->app = $app;

        return $this;
    }

    /**
     * Checks if a ctrl is set and returns true/false
     *
     * @return boolean
     */
    public function checkCtrl()
    {
        return ! empty($this->ctrl);
    }

    /**
     * Returns a camelized ctrl name
     *
     * @return string
     */
    public function getCtrl()
    {
        return $this->ctrl;
    }

    /**
     * Sets the requested ctrl manually
     *
     * @param string $ctrl
     *
     * @return \Core\Lib\Http\Router
     */
    public function setCtrl($ctrl)
    {
        $this->ctrl = $ctrl;

        return $this;
    }

    /**
     * Sets requested output format
     *
     * @param string $format Output format: xml, json or html
     *
     * @throws \ErrorException
     *
     * @return \Core\Lib\Http\Router
     */
    public function setFormat($format)
    {
        $allowed = [
            'html',
            'xml',
            'json'
        ];

        if (!in_array(strtolower($format), $allowed)) {
            Throw new \ErrorException(sprintf('Your format "%s" is not an allowed format. Use one of these formats %s', $format, implode(', ', $allowed)));
        }

        $this->format = $format;

        return $this;
    }

    /**
     * Checks if the function name is set
     */
    public function checkAction()
    {
        return ! empty($this->action);
    }

    /**
     * Returns either the requested or 'Index' (default) as action name
     *
     * @todo Routerhandler should not set a default action!
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the requested func manually
     *
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Returns requested app, ctrl and action as array
     *
     * @return array
     */
    public function getACA()
    {
        return [
            'app' => $this->app,
            'ctrl' => $this->ctrl,
            'action' => $this->action
        ];
    }

    /**
     * Boolean check if parameter exists
     *
     * @param string parametername
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
     * @return \Core\Lib\Router
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
            'is_ajax' => $this->is_ajax,
            'method' => $_SERVER['REQUEST_METHOD'],
            'app' => $this->getApp(),
            'ctrl' => $this->getCtrl(),
            'action' => $this->getAction(),
            'params' => $this->getParam(),
            'format' => $this->getFormat(),
            'raw' => $this->raw
        ];
    }

    public function clearPost()
    {
        unset($_POST);
    }
}
