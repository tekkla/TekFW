<?php
namespace Core\Lib\Amvc;

use Core\Lib\Cfg;
use Core\Lib\Request;
use Core\Lib\Content\Css;
use Core\Lib\Content\Javascript;
use Core\Lib\Content\Menu;

/**
 * Parent class for all apps
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Lib
 * @license MIT
 * @copyright 2014 by author
 * @property $di \Core\Lib\Di
 */
class App
{
	use \Core\Lib\Traits\StringTrait;
	use \Core\Lib\Traits\TextTrait;

	/**
	 * List of appnames which are already initialized
	 *
	 * @var array
	 */
	private static $init_done = [];

	private static $init_stages = [];

	/**
	 * Holds the apps name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Secure app föag
	 *
	 * @var boolean
	 */
	private $secure = false;

	/**
	 * Apps settings storage
	 *
	 * @var array
	 */
	private $settings = [
		'flags' => [],
		'config' => [],
		'routes' => []
	];

	/**
	 *
	 * @var Cfg
	 */
	protected $cfg;

	/**
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 *
	 * @var Css
	 */
	protected $css;

	/**
	 *
	 * @var Javascript
	 */
	protected $js;

	/**
	 *
	 * @var Menu
	 */
	protected $menu;

	final public function __construct($app_name, Cfg $cfg, Request $request, Css $css, Javascript $js, Menu $menu)
	{
		// Setting properties
		$this->name = $app_name;
		$this->cfg = $cfg;
		$this->request = $request;
		$this->css = $css;
		$this->js = $js;
		$this->menu = $menu;

		// Try to load settings from settings file
		$settings_file = $this->getPath() . '/Settings.php';

		if (file_exists($settings_file)) {
			$this->settings = include ($settings_file);

			// We need to check for all three settinggroups and add them if they are not set in loaded settings
			// to make the later app process easier to handle.
			$groups = [
				'flags',
				'config',
				'routes'
			];

			foreach ($groups as $group) {
				// Add emptys group when group is missing in loaded settings
				if (! array_key_exists($group, $this->settings)) {
					$this->settings[$group] = [];
				}
			}

			// Is this a secured app?
			if (in_array('secure', $this->settings['flags'])) {
				$this->secure = true;
			}
		}

		// Set default init stages which are used to prevent initiation of app parts when not needed and
		// to prevent multiple inititations when dealing with multiple app instances
		if (! isset(self::$init_stages[$this->name])) {
			self::$init_stages[$this->name] = [
				'config' => false,
				'routes' => false,
				'paths' => false,
				'hooks' => false,
				'lang' => false,
				'css' => false,
				'js' => false
			];
		}
	}

	public function init()
	{
		// Config will always be initiated. no matter what else follows.
		$this->initCfg();

		// Init paths
		$this->initPaths();

		// Apps only needs to be initiated once
		if (in_array($this->name, self::$init_done)) {
			return;
		}

		// Run init methods
		$this->initRoutes();
	}

	public function addPermissions()
	{
		if (isset($this->settings['permissions'])) {
			// We need the uncamelized name of app
			$name = $this->uncamelizeString($this->name);
			$this->di['core.sec.permissions']->addPermission($name, $this->settings['permissions']);
		}
	}

	/**
	 * Hidden method to factory mvc components like models, views or controllers
	 *
	 * @param string $name Components name
	 * @param string $type Components type
	 * @return Model|View|Controller
	 */
	private function MVCFactory($name, $type, $arguments = null)
	{
		// Here we make sure that CSS and JS will correctly and only once be initiated!
		if (! in_array($this->name, self::$init_done)) {

			// Init css and js only on non ajax requests
			if (! $this->request->isAjax()) {
				$this->initCss();
				$this->initJs();

				// Finally call a possible headers methods
				if (method_exists($this, 'addHeaders')) {
					$this->addHeaders();
				}
			}

			// Store our apps name to be initiated
			self::$init_done[] = $this->name;
		}

		// Create classname to create
		$class = $this->getNamespace() . '\\' . $type . '\\' . $name . $type;

		// By default each MVC component constructor needs at least a name and
		// this app object as argument
		$args = [
			$name,
			$this
		];

		// Add additional arguments
		if (isset($arguments)) {
			if (! is_array($arguments)) {
				$arguments = (array) $arguments;
			}

			foreach ($arguments as $arg) {
				$args[] = $arg;
			}
		}

		// Create component be using di instance factory to get sure the
		// di services the container itself is injected properly
		$component = $this->di->instance($class, $args);

		return $component;
	}

	/**
	 * Autodiscovery of the components name
	 *
	 * @return string
	 */
	private function getComponentsName()
	{
		$dt = debug_backtrace();
		$parts = array_reverse(explode('\\', $dt[1]['class']));
		return $parts[0];
	}

	/**
	 * Creates an app related model object
	 *
	 * @param string $name The models name
	 * @param string $db_container Name of the db container to use with this model
	 * @return Model
	 */
	public function getModel($name = '', $db_container = 'db.default')
	{
		if (! $name) {
			$name = $this->getComponentsName();
		}

		return $this->MVCFactory($name, 'Model', [$db_container, 'core.cfg']);
	}

	/**
	 * Creates an app related controller object.
	 *
	 * @param string $name The controllers name
	 * @return Controller
	 */
	public function getController($name)
	{
		if (! $name) {
			$name = $this->getComponentsName();
		}

		$args = [
			'core.request',
			'core.sec.security',
			'core.content.message',
			'core.content.page',
			'core.content.url',
			'core.content.menu'
		];

		return $this->MVCFactory($name, 'Controller', $args);
	}

	/**
	 * Creates an app related view object.
	 *
	 * @param string $name The viewss name
	 * @return View
	 */
	public function getView($name)
	{
		if (! $name) {
			$name = $this->getComponentsName();
		}

		return $this->MVCFactory($name, 'View');
	}

	/**
	 * Gives access on the apps config.
	 * Calling only with key returns the set value.
	 * Calling with key and value will set the apps config.
	 * Calling without any parameter will return complete app config
	 *
	 * @param string $key
	 * @param string $val
	 * @throws Error
	 * @return void boolean \Core\Lib\Cfg
	 */
	public function cfg($key = null, $val = null)
	{
		// Getting config
		if (isset($key) && ! isset($val)) {
			return $this->cfg->exists($this->name, $key) ? $this->cfg->get($this->name, $key) : false;
		}

		// Setting config
		if (isset($key) && isset($val)) {
			$this->cfg->set($this->name, $key, $val);
			return;
		}

		// Return complete config
		if (! isset($key) && ! isset($val)) {
			return $this->cfg->get($this->name);
		}

		Throw new \InvalidArgumentException('Values without keys can not be used in app config access');
	}

	/**
	 * Initializes the app config data by getting data from Cfg and adding
	 * config defaultvalues from app $cfg on demand.
	 */
	final private function initCfg()
	{
		// Add general app id an name
		$this->cfg->set($this->name, 'app', $this->name);

		// Try to get default values for not set configs
		if (isset($this->settings['config'])) {
			// Check the loaded config against the keys of the default config
			// and set the default value if no cfg value is found
			foreach ($this->settings['config'] as $key => $cfg_def) {
				// When there is no config set but a default value defined for the app,
				// the default value will be used then
				if (! $this->cfg->exists($this->name, $key) && isset($cfg_def['default'])) {
					$this->cfg->set($this->name, $key, $cfg_def['default']);
				}
			}
		}
	}

	/**
	 * Returns the namespace of the called component
	 *
	 * @return string
	 */
	public function getNamespace()
	{
		return substr(get_called_class(), 0, strrpos(get_called_class(), '\\'));
	}

	/**
	 * Returns the path of app
	 */
	public function getPath()
	{
		// Define app dir to look for subdirs
		return BASEDIR . '\\' . $this->getNamespace();
	}

	/**
	 * Returns type (app or appsec) of app
	 *
	 * @return string
	 */
	public function getAppType()
	{
		// Normal app or secure app?
		return isset($this->settings['flags']['secure']) ? 'appssec' : 'apps';
	}

	/**
	 * Initializes the apps paths by creating the paths and writing them into the apps config.
	 */
	private final function initPaths()
	{
		// Get directory path of app
		$dir = $this->getPath();

		// Get dir handle
		$handle = opendir($dir);

		// Read dir
		while (($file = readdir($handle)) !== false) {
			// No '.' or '..'
			if ('.' == $file || '..' == $file || strpos($file, '.') === 0) {
				continue;
			}

			if (is_dir($dir . '/' . $file)) {
				// Is dir and not in the excludelist? Continue if it is so.
				if (isset($this->exlude_dirs) && in_array($file, $this->exclude_dirs)) {
					continue;
				}

				// Add dir and url to app config
				$key = $this->uncamelizeString($file);

				$this->cfg->set($this->name, 'dir_' . $key, $dir . '/' . $file);
				$this->cfg->set($this->name, 'url_' . $key, $this->cfg->get('Core', 'url_' . $this->getAppType()) . '/' . $this->name . '/' . $file);
			}
		}

		// Add apps base dir and url to app config
		$this->cfg->set($this->name, 'dir_app', $this->cfg->get('Core', 'dir_' . $this->getAppType()) . '/' . $this->name);
		$this->cfg->set($this->name, 'url_app', $this->cfg->get('Core', 'url_' . $this->getAppType()) . '/' . $this->name);

		// Cleanup
		closedir($handle);

		// App specific paths to add?
		if (method_exists($this, 'addPaths')) {
			$this->addPaths();
		}
	}

	/**
	 * Initiates apps css
	 * Each app can have it's own css file.
	 * If the public property $css is set and true,
	 * at this point the app init is trying to add this css file.
	 *
	 * @return \Core\Lib\Amvc\App
	 */
	private final function initCss()
	{
		// Init css only once
		if (self::$init_stages[$this->name]['css']) {
			return;
		}

		$css_loaded = false;

		// Css flag set that indicates app has a css file?
		if (in_array('css', $this->settings['flags'])) {
			if (file_exists($this->cfg->get($this->name, 'dir_css') . '/' . $this->name . '.css')) {
				$this->css->link($this->cfg->get($this->name, 'url_css') . '/' . $this->name . '.css');
				$css_loaded = 'app';
			}
		}

		// Is there an additional css function in or app to run?
		if (method_exists($this, 'addCss')) {
			$this->addCss();
		}

		// Set flag for initiated css
		self::$init_stages[$this->name]['css'] = true;

		return $this;
	}

	/**
	 * Initiates apps javascript
	 *
	 * @throws Error
	 * @return \Core\Lib\Amvc\App
	 */
	private final function initJs()
	{
		// Init js only once
		if (self::$init_stages[$this->name]['js']) {
			return;
		}

		// Each app can (like css) have it's own javascript file. If you want to have this file included, you have to set the public property $js in
		// your app mainclass. Unlike the css include procedure, the $js property holds also the information where to include the apps .js file.
		// You hve to set this property to "scripts" (included on the bottom of website) or "header" (included in header section of website).
		// the apps js file is stored within the app folder structure in an directory named "js".
		if (isset($this->settings['flags']['js'])) {
			if (! $this->cfg->exists($this->name, 'dir_js')) {
				Throw new \RuntimeException('App "' . $this->name . '" js folder does not exist. Create the js folder in apps folder and add app js file or unset the js flag in your app mainclass.');
			}

			if (file_exists($this->cfg->get($this->name, 'dir_js') . '/' . $this->name . '.js')) {
				$this->js->file($this->cfg->get($this->name, 'url_js') . '/' . $this->name . '.js');
			} else {
				error_log('App "' . $this->name . '" Js file does not exist. Either create the js file or remove the js flag in your app mainclass.');
			}
		}

		// Js method in app to run?
		if (method_exists($this, 'addJs')) {
			$this->addJs();
		}

		// Set flag for initated js
		self::$init_stages[$this->name]['js'] = true;

		return $this;
	}

	/**
	 * Initiates in app set routes.
	 *
	 * @throws Error
	 */
	private final function initRoutes()
	{
		if (!in_array('routes', $this->settings)){

			// No routes set? Set at least index as default route
			$this->settings['routes'] = [
				[
					'name' => $this->name . '_index',
					'route' => '/',
					'ctrl' => 'Core',
					'action' => 'Index'
				]
			];

			self::$init_stages[$this->name]['routes'] = true;

			return;
		}

		// routes already initiated? Do nothing if so.
		if (self::$init_stages[$this->name]['routes'] == true) {
			return;
		}

		$routes_file = $this->cfg('dir_app') . '/Routes.php';

		if (!file_exists($routes_file)) {
			Throw new \RuntimeException('Routefile for app "' . $this->name . '" is missing');
		}

		// Load routes file
		$routes = include($routes_file);

		// Get uncamelized app name
		$app_name = $this->uncamelizeString($this->name);

		// Add routes to request handler
		foreach ($routes as $route) {
			// Create route string
			$route['route'] = $route['route'] == '/' ? '/' . $app_name : '/' . (strpos($route['route'], '../') === false ? $app_name . $route['route'] : str_replace('../', '', $route['route']));

			// Create target
			$route['target'] = [
				// App not set means app will be set automatic.
				'app' => ! isset($route['app']) ? $app_name : $route['app'],
				'ctrl' => $route['ctrl'],
				'action' => $route['action']
			];

			// The name of the route is set by the key in the routes array.
			// Is the name of type string it will be extended by the current
			// apps name.
			if (isset($route['name'])) {
				$route['name'] = (! isset($route['app']) ? $app_name : $route['app']) . '_' . $route['name'];
			}

			// Publish route
			$this->request->mapRoute($route);
		}

		self::$init_stages[$this->name]['routes'] = true;
	}

	/**
	 * Lazy textfunction so you do not have to write the apps name in the wanted textkey
	 *
	 * @param string $key The textkey you want to get the text from without need of app name in it.
	 * @see \Core\Lib\Lib::Txt() <cod
	 *      => <?php
	 *      => class Testapp_Controller_MyController extends Controller
	 *      => {
	 *      => $app = 'Testapp';
	 *
	 *      => public function MyControllerAction()
	 *      => {
	 *      => // use this
	 *      => $mytext = $this->txt('testapp_testtext');
	 *
	 *      => // or lazy
	 *      => $mytext = $this->txt('testtext');
	 *      => }
	 *      => }
	 *      =>
	 *      => </cod
	 */
	public function txt($key)
	{
		return $this->txt($key, $this->name);
	}

	/**
	 * Returns the apps config definition.
	 * If app has no definition, this method returns false.
	 *
	 * @return boolean
	 */
	public function getSettings()
	{
		if (isset($this->settings)) {
			return $this->settings;
		}

		return false;
	}

	/**
	 * Is this app a secured one?
	 *
	 * @return boolean
	 */
	public function isSecure()
	{
		return isset($this->secure) && $this->secure === true ? true : false;
	}

	/**
	 * Returns the name of this app.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns the apps id
	 *
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Returns loading state of an app
	 *
	 * @param string $app_name
	 * @return boolean
	 */
	public static function isLoaded($app_name)
	{
		return in_array($app_name, self::$loaded_apps);
	}
}
