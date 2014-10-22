<?php
namespace Core\Lib\Http;

/**
 * Router class which handles routes and request like post or get.
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 */
final class Router extends \AltoRouter
{
	use \Core\Lib\Traits\StringTrait;
	use \Core\Lib\Traits\ConvertTrait;

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
	 * storage for GET parameters
	 *
	 * @var array
	 */
	private $params = [];

	/**
	 * Name of current route
	 *
	 * @var unknown
	 */
	private $name = '';

	// ---------------------------------------------------------------------------
	// ROUTE HANDLING
	// ---------------------------------------------------------------------------

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
	 * @param string $router_url
	 * @param string $router_method
	 * @return array boolean with route information on success, false on failure (no match).
	 */
	public function match($router_url = null, $router_method = null)
	{
		// Try to match request
		$match = parent::match($router_url, $router_method);

		if ($match)
		{
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

			$this->params = $match['params'];
		}

		return $match;
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
	 *
	 * @return boolean
	 */
	public function isAjax($bool = null)
	{
		if (isset($bool)) {
			$this->is_ajax = true;
			return $this;
		}

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
	 * Check set of app property
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
		return isset($this->app) ? $this->app : false;
	}

	/**
	 * Set appname manually
	 *
	 * @param string $val
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
		return isset($this->params[$key]);
	}

	/**
	 * Sets one or mor request parameter.
	 * Set $arg1 as assoc array to add multiple parameter.
	 * Both args ($arg1 and $arg2) set means to add a parameter by key ($arg1) and value ($arg2)
	 *
	 * @param string|array $arg1
	 * @param string $arg2
	 * @return \Core\Lib\Router
	 */
	public function addParam($arg1, $arg2 = null)
	{
		if ($arg2 === null && is_array($arg1)) {
			$arg1 = $this->convertObjectToArray($arg1);

			foreach ($arg1 as $key => $val)
				$this->params[$key] = $val;
		}

		if ($arg2 !== null)
			$this->params[$arg1] = $this->convertObjectToArray($arg2);

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

	public function isPost()
	{
		return isset($_POST) && isset($_POST['app']);
	}

	/**
	 * Returns the value of $_POST[web][appname][ctrlname][key]
	 *
	 * @param string $key
	 */
	public function getPost($app_name = '', $model_name = '')
	{
		if (! $this->isPost())
			return false;

			// Use values provided by request for missing app and model name
		if (! $app_name || ! $model_name) {
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
	 *
	 * @return array
	 */
	public function getRawPost()
	{
		return $this->post_raw;
	}

	/**
	 * Returns the complete processed post object
	 *
	 * @return \Core\Lib\Data\Data
	 */
	public function getCompletePost()
	{
		return $this->post;
	}

	/**
	 * Returns true if $_POST[app][appname][modelname] is in the processed post data
	 *
	 * @param string $app
	 */
	public function checkPost($app_name = null, $model_name = null)
	{
		if (! isset($app_name) || ! isset($model_name)) {
			$app_name = $this->getApp();
			$model_name = $this->getCtrl();
		}

		$app_name = $this->uncamelizeString($app_name);
		$model_name = $this->uncamelizeString($model_name);

		return isset($this->post->{$app_name}) && isset($this->post->{$app_name}->{$model_name});
	}

	/**
	 * Clears the post storage
	 */
	public function clearPost()
	{
		$this->post = new \stdClass();
	}

	/**
	 * Returns all request related data in one array
	 * @return array
	 */
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
