<?php
namespace Core\Lib\Amvc;

use Core\Lib\Cfg;
use Core\Lib\Request;
use Core\Lib\Content\Css;
use Core\Lib\Content\Javascript;
use Core\Lib\Content\Menu;
use Core\Lib\Security\Permission;

/**
 * Parent class for all apps
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package TekFW
 * @subpackage Lib
 * @license MIT
 * @copyright 2014 by author
 * @property $di \Core\Lib\Di
 */
class App
{
	use\Core\Lib\Traits\StringTrait;
	use\Core\Lib\Traits\TextTrait;

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
	private $settings = [];

	/**
	 * Stores app path
	 *
	 * @var string
	 */
	private $path = '';

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

	/**
	 *
	 * @var Permission
	 */
	private $permission;


	/**
	 * Constructor
	 *
	 * @param string $app_name
	 * @param Cfg $cfg
	 * @param Request $request
	 * @param Css $css
	 * @param Javascript $js
	 * @param Menu $menu
	 */
	final public function __construct($app_name, Cfg $cfg, Request $request, Css $css, Javascript $js, Menu $menu, Permission $permission)
	{
		// Setting properties
		$this->name = $app_name;
		$this->cfg = $cfg;
		$this->request = $request;
		$this->css = $css;
		$this->js = $js;
		$this->menu = $menu;
		$this->permission = $permission;

		// Set path property which is used on including additional app files like settings, routes, config etc
		$this->path = BASEDIR . '\\' . $this->getNamespace();

		// Try to load settings from settings file
		$settings_file = $this->path . '/Settings.php';

		// Is there a settingsfile to load?
		if (file_exists($settings_file)) {

			// Include it
			$this->settings = include ($settings_file);

			// Is this a secured app?
			if (in_array('secure', $this->settings)) {
				$this->secure = true;
			}
		}

		// Set default init stages which are used to prevent initiation of app parts when not needed and
		// to prevent multiple initiations when dealing with multiple app instances
		if (! isset(self::$init_stages[$this->name])) {
			self::$init_stages[$this->name] = [
				'config' => false,
				'routes' => false,
				'paths' => false,
				'perms' =>false,
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
		$this->initPermissions();
		$this->initLanguage();
	}

	/**
	 * Checks app settings for permissions to load, checks for existing permissions
	 * file and adds permissions to core permission service. Throws runtimeexception
	 * when permissions are set to be loaded but no permissions file is found.
	 *
	 * @throws \RuntimeException
	 */
	private function initPermissions()
	{
		// We need lowercase app name
		$app_name = $this->uncamelizeString($this->name);

		// Add admin permission by default
		$this->permission->addPermission($app_name, 'admin');

		// Having a config means we have to add an admin permission
		if (in_array('config', $this->settings)) {
			$this->permission->addPermission($app_name, 'config');
		}

		// Do we have permissions do add?
		if (in_array('permissions', $this->settings)) {

			// Include permission file
			$permissions_file = $this->path . '/Permissions.php';

			// Should we throw an exception due to missing permissions file?
			if (! file_exists($permissions_file)) {
				Throw new \RuntimeException('The permission file for app "' . $this->name . '" is missing. Add Permission.php to your app root folder or remove permission flag in your app settings file.');
			}

			// Include permission file
			$permissions = include ($permissions_file);

			// Any permissions found?
			if (! empty($permissions)) {

				// We need the uncamelized name of app
				$name = $this->uncamelizeString($this->name);

				// Add permissions to permission service
				$this->permission->addPermission($name, $permissions);
			}

			self::$init_stages[$this->name]['permissions'] = true;
		}
	}

	/**
	 * Inits the language file according to the current language the site/user uses
	 *
	 * @throws \RuntimeException
	 */
	private function initLanguage()
	{
		// Init only once
		if (self::$init_stages[$this->name]['lang']) {
			return;
		}

		// Do we have permissions do add?
		if (in_array('language', $this->settings)) {

			// Check
			if (!$this->cfg->exists($this->name, 'dir_language')) {
				Throw new \RuntimeException('Languagefile for app "' . $this->name . '" has to be loaded but no Language folder was found.');
			}


			// Include permission file
			$language_file = $this->cfg('dir_language') . '/' . $this->name . '.' . $this->cfg->get('Core', 'language') . '.php';

			$this->di['core.content.lang']->loadLanguageFile($this->name, $language_file);

			self::$init_stages[$this->name]['language'] = true;
		}
	}

	/**
	 * Hidden method to factory mvc components like models, views or controllers.
	 *
	 * @param string $name Components name
	 * @param string $type Components type
	 *
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

		// Create classname of component to create
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
		return $this->di->instance($class, $args);
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
	 *
	 * @return Model
	 */
	public function getModel($name = '', $db_container = 'db.default')
	{
		if (! $name) {
			$name = $this->getComponentsName();
		}

		return $this->MVCFactory($name, 'Model', [
			$db_container,
			'core.cfg'
		]);
	}

	/**
	 * Creates an app related controller object.
	 *
	 * @param string $name The controllers name
	 *
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
			'core.content.menu',
			'core.content.html.factory'
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
	 *
	 * @return void boolean \Core\Lib\Cfg
	 *
	 * @throws \InvalidArgumentException
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
		if (in_array('config', $this->settings)) {

			// Path to config file
			$config_file = $this->path . '/Config.php';

			// Check config file exists
			if (! file_exists($config_file)) {
				Throw new \RuntimeException('Config file for app "' . $this->name . '" is missing.');
			}

			// Load routes file
			$config = include ($config_file);

			// Check the loaded config against the keys of the default config
			// and set the default value if no cfg value is found
			foreach ($config as $key => $cfg_def) {

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
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Returns type (app or appsec) of app
	 *
	 * @return string
	 */
	public function getAppType()
	{
		// Normal app or secure app?
		return in_array('secure', $this->settings) ? 'appssec' : 'apps';
	}

	/**
	 * Initializes the apps paths by creating the paths and writing them into the apps config.
	 */
	private final function initPaths()
	{
		// Get directory path of app
		$dir = $this->path;

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
	 * Initiates apps css. Each app can have it's own css file. The css file needs to be placed in an Css folder within
	 * the apps folder. App settings need a css flag, otherwise the css file won't be loaded.
	 *
	 * @return \Core\Lib\Amvc\App
	 */
	private final function initCss()
	{
		// Init css only once
		if (self::$init_stages[$this->name]['css']) {
			return;
		}

		// Css flag set that indicates app has a css file?
		if (in_array('css', $this->settings)) {

			// Check for existance of apps css file
			if (!file_exists($this->cfg->get($this->name, 'dir_css') . '/' . $this->name . '.css')) {
				Throw new \RuntimeException('App "' . $this->name . '" css file does not exist. Either create the js file or remove the css flag in your app settings.');
			}

			// Create css file link
			$this->css->link($this->cfg->get($this->name, 'url_css') . '/' . $this->name . '.css');
		}

		// Is there an additional css function in app to run?
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
	 * @return \Core\Lib\Amvc\App
	 *
	 * @throws \RuntimeException
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
		if (in_array('js', $this->settings)) {

			if (!file_exists($this->cfg->get($this->name, 'dir_js') . '/' . $this->name . '.js')) {
				Throw new \RuntimeException('App "' . $this->name . '" js file does not exist. Either create the js file or remove the js flag in your app mainclass.');
			}

			$this->js->file($this->cfg->get($this->name, 'url_js') . '/' . $this->name . '.js');
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
	 * @throws \RuntimeException
	 */
	private final function initRoutes()
	{
		if (! in_array('routes', $this->settings)) {

			// No routes set? Map at least index as default route
			$route = [
				'name' => $this->name . '_index',
				'route' => '/',
				'target' => [
					'app' => $this->name,
					'ctrl' => 'Index',
					'action' => 'Index'
				]
			];

			$this->request->mapRoute($route);

			self::$init_stages[$this->name]['routes'] = true;

			return;
		}

		// routes already initiated? Do nothing if so.
		if (self::$init_stages[$this->name]['routes'] == true) {
			return;
		}

		// Path to routes file
		$routes_file = $this->path . '/Routes.php';

		// Check routes file existance
		if (! file_exists($routes_file)) {
			Throw new \RuntimeException('Routes file for app "' . $this->name . '" is missing.');
		}

		// Load routes file
		$routes = include ($routes_file);

		// Get uncamelized app name
		$app_name = $this->uncamelizeString($this->name);

		// Map routes to request handler router
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
	 * Returns the apps config definition. Returns boolean false on empty settings.
	 *
	 * @return array|boolean
	 */
	public function getSettings()
	{
		return $this->settings ? $this->settings : false;
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
	 * Returns loading state of an app.
	 *
	 * @param string $app_name
	 *
	 * @return boolean
	 */
	public static function isLoaded($app_name)
	{
		return in_array($app_name, self::$loaded_apps);
	}

	/**
	 * Returns the init stagelist of this app.
	 *
	 * @return array
	 */
	public function getInitState()
	{
		return self::$init_stages[$this->name];
	}

	/**
	 * Registers an app related di service.
	 *
	 * @param string $name Name of service
	 * @param string $class Class name this service uses
	 * @param array $args Optional arguments
	 *
	 * @return \Core\Lib\Amvc\App
	 */
	protected function registerService($name, $class, $args = [])
	{
		$this->di->mapService('app.' . $this->name . '.' . $name, $class, $args);
		return $this;
	}

	/**
	 * Registers an app related di class factor.
	 *
	 * @param string $name Name of factory
	 * @param string $class Class name this service uses
	 * @param array $args Optional arguments
	 *
	 * @return \Core\Lib\Amvc\App
	 */
	protected function registerFactory($name, $class, $args = [])
	{
		$this->di->mapFactory('app.' . $this->name . '.' . $name, $class, $args);
		return $this;
	}
}
