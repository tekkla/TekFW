<?php
namespace Core\Lib;

use Core\Lib\Data\Data;
/**
 * Request class which handles routes and request like post or get.
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 *
 * ------------------------------------------------------------------------
 * Routing based on AltoRouter
 * https://github.com/dannyvankooten/AltoRouter
 *
 * Copyright 2012-2013 Danny van Kooten hi@dannyvankooten.com
 * License MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 *
 * Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR
 * OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 * ------------------------------------------------------------------------
 */
final class Request
{
	use \Core\Lib\Traits\StringTrait;
	use \Core\Lib\Traits\ConvertTrait;

	/**
	 * Status flag ajax
	 * @var bool
	 */
	private $is_ajax = false;

	/**
	 * Requested app
	 * @var string
	 */
	private $app = '';

	/**
	 * Requested conroller
	 * @var string
	 */
	private $ctrl = '';

	/**
	 * Requestet action
	 * @var string
	 */
	private $action = '';

	/**
	 * Target parameter used in AJAX requestshandling
	 * @var string
	 */
	private $target = '';

	/**
	 * storage for GET parameters
	 * @var array
	 */
	private $params = [];

	/**
	 * Name of current route
	 * @var unknown
	 */
	private $name = '';

	/**
	 * Storage for POST values
	 * @var Data
	 */
	private $post = false;

	/**
	 * Storage for unprocessed POST values
	 * @var Data
	 */
	private $post_raw = false;

	/**
	 * Route storage
	 * @var array
	 */
	private $routes = [];

	/**
	 * Named routes storage
	 * @var array
	 */
	private $named_routes = [];

	// PCRE matchtypes
	private $match_types = [
		'i' => '[0-9]++',
		'a' => '[0-9A-Za-z]++',
		'h' => '[0-9A-Fa-f]++',
		'*' => '.+?',
		'**' => '.++',
		'' => '[^/\.]++'
	];

	private $match = false;

	// ---------------------------------------------------------------------------
	// ROUTE HANDLING
	// ---------------------------------------------------------------------------

	/**
	 * Add multiple routes at once from array in the following format:
	 *
	 * $routes = [
	 * 	[$method, $route, $target, $name)
	 * );
	 *
	 * @param array $routes
	 * @return void
	 * @author Koen Punt
	 */
	public function addRoutes($routes)
	{
		if (!is_array($routes) && !$routes instanceof \Traversable) {
			Throw new \InvalidArgumentException('Routes should be an array or an instance of Traversable');
		}

		foreach ( $routes as $route ) {
			call_user_func_array([
				$this,
				'mapRoute'
			], $route);
		}
	}

	/**
	 * Add named match types.
	 * It uses array_merge so keys can be overwritten.
	 * @param array $match_types The key is the name and the value is the regex.
	 */
	public function addMatchTypes($match_types)
	{
		$this->match_types = array_merge($this->match_types, $match_types);
	}

	/**
	 * Map a route to a target
	 * @param string $method One of 4 HTTP Methods, or a pipe-separated list of multiple HTTP Methods (GET|POST|PUT|DELETE)
	 * @param string $route The route regex, custom regex must start with an @. You can use multiple pre-set regex filters, like [i:id]
	 * @param mixed $target The target where this route should point to. Can be anything.
	 * @param string $name Optional name of this route. Supply if you want to reverse route this url in your application.
	 * @param array $access Optional array with names of SMF access rights.
	 */
	public function mapRoute($route)
	{
		if (!isset($route['target'])) {
			Throw new \InvalidArgumentException('A route needs a target', 6004, $route);
		}
		
			// Is this a named route?
		if (isset($route['name'])) 
		{
			if (array_key_exists($route['name'], $this->named_routes))
			{
				throw new \InvalidArgumentException('Route "' . $route['name'] . '" has already been declared.', 6002, $route);
			}
			else
			{
				$named_route = [
					'route' => $route['route']
				];

				if (isset($route['access'])) {
					$named_route['access'] = $route['access'];
				}

				$this->named_routes[$route['name']] = $named_route;
			}
		}

		// Prepare route definition
		$route_definition = [];

		// Check for set route method
		$route_definition[0] = isset($route['method']) ? $route['method'] : 'GET';

		// Extend route with basepath
		$route_definition[1] = $route['route'];

		// Set target
		$route_definition[2] = $route['target'];

		// Name set?
		$route_definition[3] = isset($route['name']) ? $route['name'] : '';

		// Access set?
		$route_definition[4] = isset($route['access']) ? $route['access'] : [];

		// Stor new route
		$this->routes[] = $route_definition;

		return $this;
	}

	/**
	 * Reversed routing
	 * Generate the URL for a named route.
	 * Replace regexes with supplied parameters
	 * @param string $route_name The name of the route.
	 * @param array @params Associative array of parameters to replace placeholders with.
	 * @return string The URL of the route with named parameters in place.
	 * @todo Add access check
	 */
	public function getRouteUrl($route_name, $params = [])
	{
		// Check if named route exists
		if (!isset($this->named_routes[$route_name])){
			Throw new \InvalidArgumentException('Route "' . $route_name . '" does not exist.', 6000);
		}
		
		// Replace named parameters
		$route = $this->named_routes[$route_name];

		// Accesscheck set?
		if (isset($route['access']) && !$this->security->checkAccess($route['access'])) {
			return null;
		}
		
		$url = $route['route'];

		if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route['route'], $matches, PREG_SET_ORDER))
		{
			foreach ( $matches as $match )
			{
				list($block, $pre, $type, $param, $optional) = $match;

				if ($pre) {
					$block = substr($block, 1);
				}

				if (isset($params[$param])){
					$url = str_replace($block, $params[$param], $url);
				}
				elseif ($optional){
					$url = str_replace($pre . $block, '', $url);
				}
				else {
					Throw new \InvalidArgumentException('Parameter missing.', 6001);
				}
			}
		}

		return BASEURL . $url;
	}

	/**
	 * Match a given request url against stored routes
	 * @param string $request_url
	 * @param string $request_method
	 * @return array boolean with route information on success, false on failure (no match).
	 */
	public function processRequest($request_url = null, $request_method = null)
	{
		$params = [];
		$match = false;

		// set Request Url if it isn't passed as parameter
		if ($request_url === null) {
			$request_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
		}

			// Strip query string (?a=b) from Request Url
		if (( $strpos = strpos($request_url, '?') ) !== false){
			$request_url = substr($request_url, 0, $strpos);
		}
		
			// Framework ajax.js adds automatically an /ajax flag @ the end of the requested URI.
			// Here we check for this flag, remembers if it's present and then remove the flag
			// so the following URI processing runs without flaw.
		if (substr($request_url, -5) == '/ajax')
		{
			$this->addParam('ajax', true);
			$request_url = str_replace('/ajax', '', $request_url);
			$this->is_ajax = true;
		}

		// set Request Method if it isn't passed as a parameter
		if ($request_method === null){
			$request_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
		}

		foreach ( $this->routes as $handler )
		{

			list($method, $_route, $target, $name, $access) = $handler;

			// Method seems to match. First to do is a possible access check
			// If check fails, the rest of our routes will be checked
			#if (isset($access) && !allowedTo($access))
			#	continue;

			$methods = explode('|', $method);
			$method_match = false;

			// Check if request method matches. If not, abandon early. (CHEAP)
			foreach ( $methods as $method )
			{
				if (strcasecmp($request_method, $method) === 0)
				{
					$method_match = true;
					break;
				}
			}

			// Method did not match, continue to next route.
			if (!$method_match){
				continue;
			}

				// Check for a wildcard (matches all)
			if ($_route === '*'){
				$match = true;
			}
			elseif (isset($_route[0]) && $_route[0] === '@'){
				$match = preg_match('`' . substr($_route, 1) . '`u', $request_url, $params);
			}
			else
			{
				$route = null;
				$regex = false;
				$j = 0;
				$n = isset($_route[0]) ? $_route[0] : null;
				$i = 0;

				// Find the longest non-regex substring and match it against the URI
				while ( true )
				{
					if (!isset($_route[$i])){
						break;
					}
					elseif (false === $regex)
					{
						$c = $n;
						$regex = $c === '[' || $c === '(' || $c === '.';

						if (false === $regex && false !== isset($_route[$i + 1]))
						{
							$n = $_route[$i + 1];
							$regex = $n === '?' || $n === '+' || $n === '*' || $n === '{';
						}

						if (false === $regex && $c !== '/' && ( !isset($request_url[$j]) || $c !== $request_url[$j] )){
							continue 2;
						}
						
						$j++;
					}
					$route .= $_route[$i++];
				}

				$regex = $this->compileRoute($route);
				$match = preg_match($regex, $request_url, $params);
			}

			if ($match == true || $match  > 0)
			{
				if ($params)
				{
					foreach ( $params as $key => $value ){
						if ($key == '0' . $key) {
							unset($params[$key]);
						}
					}
				}

				$this->match = [
					'target' => $target,
					'params' => $params,
					'name' => $name
				];

				$this->name = $name;

				// Map target results to request properties
				foreach ( $target as $key => $val )
				{
					if (property_exists($this, $key)){
						$this->{$key} = $this->camelizeString($val);
					}
				}

				// When no target ctrl defined in route but provided by parameter
				// we use the parameter as requested ctrl
				if (!$this->ctrl && isset($params['ctrl'])){
					$this->ctrl = $this->camelizeString($params['ctrl']);
				}
				
					// Same for action as for ctrl
				if (!$this->action && isset($params['action'])){
					$this->action = $this->camelizeString($params['action']);
				}

				$this->params = $params;

				// Finally try to process possible posted data
				if (!empty($_POST))
				{
					$this->post = new Data($_POST['app']);
					$this->post_raw = $_POST;
				}

				return $this;
			}
		}

		$this->match = false;

		#Throw new \RuntimeException('No matching route found.', 6001);

		return $this;
	}

	/**
	 * Compile the regex for a given route (EXPENSIVE)
	 */
	private function compileRoute($route)
	{
		if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER))
		{
			$match_types = $this->match_types;

			foreach ( $matches as $match )
			{
				list($block, $pre, $type, $param, $optional) = $match;

				if (isset($match_types[$type])) {
					$type = $match_types[$type];
				}

				if ($pre === '.') {
					$pre = '\.';
				}

					// Older versions of PCRE require the 'P' in (?P<name)
				$pattern = '(?:' . ( $pre !== '' ? $pre : null ) . '(' . ( $param !== '' ? "?P<$param>" : null ) . $type . '))' . ( $optional !== '' ? '?' : null );

				$route = str_replace($block, $pattern, $route);
			}
		}

		return "`^$route$`u";
	}

	/**
	 * Checks for a route with the name of the parameter
	 * @param string $route
	 * @return boolean
	 */
	public function checkRoute($route)
	{
		return isset($this->routes[$route]);
	}

	/**
	 * Returns the name of the current active route
	 */
	public function getCurrentRoute()
	{
		return $this->name;
	}

	/**
	 * Checks for an ajax request and returns boolean true or false
	 * @return boolean
	 */
	public function isAjax($bool = null)
	{
		if (isset($bool))
		{
			$this->is_ajax = true;
			return $this;
		}

		return $this->is_ajax;
	}

	/**
	 * Checks if the request is a (A)pp(C)ontroller(A)ction call
	 * @return boolean
	 */
	public function isCall()
	{
		return $this->checkApp() && $this->checkCtrl() && $this->checkAction();
	}

	/**
	 * Check set of app property
	 */
	public function checkApp()
	{
		return isset($this->app);
	}

	/**
	 * Returns a camelized app name
	 * @return string
	 */
	public function getApp()
	{
		return isset($this->app) ? $this->app : false;
	}

	/**
	 * Set appname manually
	 * @param string $val
	 */
	public function setApp($app)
	{
		$this->app = $app;
		return $this;
	}

	/**
	 * Checks if a ctrl is set and returns true/false
	 * @return boolean
	 */
	public function checkCtrl()
	{
		return !empty($this->ctrl);
	}

	/**
	 * Returns a camelized ctrl name
	 * @return string
	 */
	public function getCtrl()
	{
		return isset($this->ctrl) ? $this->ctrl : 'Index';
	}

	/**
	 * Sets the requested ctrl manually
	 * @param string $ctrl
	 */
	public function setCtrl($ctrl)
	{
		$this->ctrl = $ctrl;
		return $this;
	}

	/**
	 * Checks if the function name is set
	 */
	public function checkAction()
	{
		return !empty($this->action);
	}

	/**
	 * Returns either the requested or 'Index' (default) as action name
	 * @todo Requesthandler should not set a default action!
	 */
	public function getAction()
	{
		if (!isset($this->action)) {
			$this->action = 'Index';
		}

		return $this->action;
	}

	/**
	 * Set the requested func manually
	 * @param string $action
	 */
	public function setAction($action)
	{
		$this->action = $action;
		return $this;
	}

	/**
	 * Returns requested app, ctrl and action as array
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
	 * @param string parametername
	 * @return boolean
	 */
	public function checkParam($key)
	{
		return isset($this->params[$key]);
	}

	/**
	 * Sets one or mor request parameter. Set $arg1 as assoc array to add multiple parameter.
	 * Both args ($arg1 and $arg2) set means to add a parameter by key ($arg1) and value ($arg2)
	 * @param string|array $arg1
	 * @param string $arg2
	 * @return \Core\Lib\Request
	 */
	public function addParam($arg1, $arg2 = null)
	{
		if ($arg2 === null && is_array($arg1))
		{
			$arg1 = $this->convertObjectToArray($arg1);

			foreach ( $arg1 as $key => $val )
				$this->params[$key] = $val;
		}

		if ($arg2 !== null)
			$this->params[$arg1] = $this->convertObjectToArray($arg2);

		return $this;
	}

	/**
	 * Returns the value of a paramter as long as it exists.
	 * @param string $key
	 */
	public function getParam($key)
	{
		return isset($this->params[$key]) ? $this->params[$key] : false;
	}

	/**
	 * Returns the complete paramslist
	 * @return array
	 */
	public function getAllParams()
	{
		return $this->params;
	}

	public function isPost()
	{
		return isset($_POST) && isset($_POST['app']);
	}

	/**
	 * Returns the value of $_POST[web][appname][ctrlname][key]
	 * @param string $key
	 */
	public function getPost($app_name = '', $model_name = '')
	{
		if (!$this->isPost())
			return false;

		// Use values provided by request for missing app and model name
		if (!$app_name || !$model_name)
		{
			$app_name = $this->getApp();
			$model_name = $this->getCtrl();
		}

		$app_name = $this->uncamelizeString($app_name);
		$model_name = $this->uncamelizeString($model_name);

		if (isset($this->post->{$app_name}->{$model_name}))
			return $this->post->{$app_name}->{$model_name};
		else
			return false;
	}

	/**
	 * Returns the complete raw post array
	 * @return array
	 */
	public function getRawPost()
	{
		return $this->post_raw;
	}

	/**
	 * Returns the complete processed post object
	 * @return \Core\Lib\Data\Data
	 */
	public function getCompletePost()
	{
		return $this->post;
	}

	/**
	 * Returns true if $_POST[app][appname][modelname] is in the processed post data
	 * @param string $app
	 */
	public function checkPost($app_name = null, $model_name = null)
	{
		if (!isset($app_name) || !isset($model_name))
		{
			$app_name = $this->getApp();
			$model_name = $this->getCtrl();
		}

		$app_name = $this->uncamelizeString($app_name);
		$model_name = $this->uncamelizeString($model_name);

		return isset($this->post->{$app_name}) && isset($this->post->{$app_name}->{$model_name});
	}

	public function clearPost()
	{
		$this->post = new \stdClass();
	}

	public function getStatus()
	{
		return [
			'is_ajax' => $this->is_ajax,
			'app' => $this->getApp(),
			'ctrl' => $this->getCtrl(),
			'action' => $this->getAction(),
			'params' => $this->getAllParams()
		];
	}
}
