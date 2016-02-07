<?php
namespace Core\Lib\Amvc;

// DI Service
use Core\Lib\DI;

// Cfg Service
use Core\Lib\Cfg\Cfg;

// Security Libs
use Core\Lib\Security\Security;
use Core\Lib\Security\Permission;

// Page
use Core\Lib\Page\Page;

// Router Libs
use Core\Lib\Router\Router;
use Core\Lib\Router\UrlTrait;

// Data Libs
use Core\Lib\Data\Container\Container;

// Common Traits
use Core\Lib\Traits\StringTrait;
use Core\Lib\Language\TextTrait;
use Core\Lib\Cfg\CfgTrait;

/**
 * App.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 *
 * @todo Switch to options porperty to set app specfic flags.
 */
class App
{

    use TextTrait;
    use CfgTrait;
    use UrlTrait;
    use StringTrait {
        StringTrait::stringShorten insteadof UrlTrait;
        StringTrait::stringCamelize insteadof UrlTrait;
        StringTrait::stringUncamelize insteadof UrlTrait;
        StringTrait::stringNormalize insteadof UrlTrait;
        StringTrait::stringIsSerialized insteadof UrlTrait;
    }

    /**
     * List of appnames which are already initialized
     *
     * @var array
     */
    private static $init_done = [];

    /**
     * Storage for init stages
     *
     * @var array
     */
    private static $init_stages = [];

    /**
     * Holds the apps name
     *
     * @var string
     */
    protected $name;

    /**
     * Secure app flag
     *
     * @var boolean
     */
    protected $secure = false;

    /**
     * Falg for using language system
     *
     * @var boolean
     */
    protected $language = false;

    /**
     * Flag for using seperate css file
     *
     * @var boolean
     */
    protected $css_file = false;

    /**
     * Flag for using seperate js file
     *
     * @var boolean
     */
    protected $js_file = false;

    /**
     * Apps routes storage
     *
     * @var array
     */
    protected $routes = [];

    /**
     * Apps default config storage
     *
     * @var array
     */
    protected $config = [];

    /**
     * Apps permission storage
     *
     * @var array
     */
    protected $permissions = [];

    /**
     * Stores app path
     *
     * @var string
     */
    private $path = '';

    /**
     * Config service
     *
     * @var Cfg
     */
    protected $cfg;

    /**
     * Router service
     *
     * @var Router
     */
    protected $router;

    /**
     *
     * @var Page
     */
    protected $page;

    /**
     *
     * @var Security
     */
    protected $security;

    /**
     *
     * @var DI
     */
    public $di;

    /**
     * Constructor
     *
     * @param string $app_name
     *            This apps name
     * @param Cfg $cfg
     *            Cfg dependency
     * @param Router $router
     *            Router dependency
     * @param Page $page
     *            Page dependency
     * @param Security $security
     *            Security dependence
     * @param DI $di
     *            Visible DI dependency
     */
    final public function __construct($app_name, Cfg $cfg, Router $router, Page $page, Security $security, DI $di)
    {
        // Setting properties
        $this->name = $app_name;
        $this->cfg = $cfg;
        $this->router = $router;
        $this->page = $page;
        $this->security = $security;
        $this->di = $di;

        // Set path property which is used on including additional app files like settings, routes, config etc
        $this->path = BASEDIR . '/' . str_replace('\\', '/', $this->getNamespace());

        // Set default init stages which are used to prevent initiation of app parts when not needed and
        // to prevent multiple initiations when dealing with multiple app instances
        if (! isset(self::$init_stages[$this->name])) {
            self::$init_stages[$this->name] = [
                'config' => false,
                'routes' => false,
                'paths' => false,
                'perms' => false,
                'lang' => false,
                'css' => false,
                'js' => false
            ];
        }

        // Config will always be initiated. no matter what else follows
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

        // Finally call a possible app specific Init() method
        if (method_exists($this, 'Init')) {
            $this->Init();
        }
    }

    /**
     * Checks app settings for permissions to load, checks for existing permissions file
     * and adds permissions to core permission service
     */
    final private function initPermissions()
    {
        // We need lowercase app name
        $app_name = $this->stringUncamelize($this->name);

        // Add admin permission by default
        $this->security->permission->addPermission($app_name, 'admin');

        // Having a config means we have to add an admin permission
        if ($this->config) {
            $this->security->permission->addPermission($app_name, 'config');
        }

        // Add permissions to permission service
        if ($this->permissions) {
            $this->security->permission->addPermission($app_name, $this->permissions);
        }

        // Set flat that permission init is done
        self::$init_stages[$this->name]['permissions'] = true;
    }

    /**
     * Inits the language file according to the current language the site/user uses
     *
     * @throws AppException
     */
    final private function initLanguage()
    {
        // Init only once
        if (self::$init_stages[$this->name]['lang']) {
            return;
        }

        // Do we have permissions do add?
        if ($this->language) {

            // Check
            if (! isset($this->cfg->data[$this->name]['dir.language'])) {
                Throw new AppException(sprintf('Languagefile of app "%s" has to be loaded but no Language folder was found.', $this->name));
            }

            // Get reference to language service
            $language_service = $this->di->get('core.language');

            // Always load english language files.
            $language_file = $this->cfg->data[$this->name]['dir.language'] . '/en.php';
            $language_service->loadLanguageFile($this->name, $language_file);

            // After that load set languenage file which can override the loaded english string.
            $default_language = $this->cfg->data['Core']['site.language.default'];

            if ($default_language != 'en') {

                $language_file = $this->cfg->data[$this->name]['dir.language'] . '/' . $default_language . '.php';
                $language_service->loadLanguageFile($this->name, $language_file);
            }

            self::$init_stages[$this->name]['language'] = true;
        }
    }

    /**
     * Hidden method to factory mvc components like models, views or controllers
     *
     * @param string $name
     *            Components name
     * @param string $type
     *            Components type
     *
     * @return Model|View|Controller|Container
     */
    final private function MVCFactory($name, $type, $arguments = null)
    {
        // Here we make sure that CSS and JS will correctly and only once be initiated!
        if (! in_array($this->name, self::$init_done)) {

            // Init css and js only on non ajax requests
            if (! $this->router->isAjax()) {
                $this->initCss();
                $this->initJs();
            }

            // Store our apps name to be initiated
            self::$init_done[] = $this->name;
        }

        // Create classname of component to create
        $class = $this->getNamespace() . '\\' . $type . '\\' . $name . $type;

        // Check existance of container objects because they are optional
        if ($type == 'Container') {

            $container_class_path = BASEDIR . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, explode('\\', $class)) . '.php';

            if (! file_exists($container_class_path)) {
                return false;
            }

            return new $class();
        }

        // By default each MVC component constructor needs at least a name and this app object as argument
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
    final private function getComponentsName()
    {
        $dt = debug_backtrace();
        $parts = array_reverse(explode('\\', $dt[1]['class']));
        return $parts[0];
    }

    /**
     * Creates an app related model object
     *
     * @param string $name
     *            The models name
     * @param string $db_container
     *            Name of the db container to use with this model
     *
     * @return Model
     */
    final public function getModel($name = '')
    {
        if (empty($name)) {
            $name = $this->getComponentsName();
        }

        return $this->MVCFactory($name, 'Model');
    }

    /**
     * Creates an app related controller object
     *
     * @param string $name
     *            The controllers name
     *
     * @return Controller
     */
    final public function getController($name = '')
    {
        if (empty($name)) {
            $name = $this->getComponentsName();
        }

        $args = [
            'core.router',
            'core.http',
            'core.security',
            'core.page',
            'core.html.factory',
            'core.cache',
            'core.ajax'
        ];

        return $this->MVCFactory($name, 'Controller', $args);
    }

    /**
     * Creates an app related view object.
     *
     * @param string $name
     *            The viewss name
     * @return View
     */
    final public function getView($name = '')
    {
        if (empty($name)) {
            $name = $this->getComponentsName();
        }

        return $this->MVCFactory($name, 'View');
    }

    /**
     * Creates an app related container object.
     *
     * @param string $name
     *            The controllers name
     *
     * @return Controller
     */
    final public function getContainer($name = '')
    {
        // Autodiscover componentsname on demand
        if (empty($name)) {
            $name = $this->getComponentsName();
        }

        // Init args array
        $args = [];

        /* @var $container \Core\Lib\Data\Container\Container */
        $container = $this->MVCFactory($name, 'Container', $args);

        if (! $container) {
            Throw new AppException(sprintf('Container "%s" does not exist.', $name));
        }

        // We need our action so we can call a possible existing function that gives us only
        // those fields needed for this action
        $action = $this->router->getAction();

        // Do we have such method?
        if (! method_exists($container, $action)) {

            // ... and try to find and run Index method when no matching action is found
            $action = method_exists($container, 'Index') ? 'Index' : 'useAllFields';
        }

        // ... and call matching container action when method exists
        $container->$action();

        // finally try to parse field defintion
        $container->parseFields();

        return $container;
    }

    /**
     * Returns apps default config
     *
     * @return array
     */
    final public function getConfig()
    {
        return $this->config;
    }

    /**
     * Boolean check for existing app config.
     *
     * @return boolean
     */
    final public function hasConfig()
    {
        return ! empty($this->config);
    }

    /**
     * Initializes the app config data by getting data from Cfg and adding
     * config defaultvalues from app $cfg on demand.
     */
    final private function initCfg()
    {
        // Add general app id an name
        $this->cfg->data[$this->name]['app.name'] = $this->name;

        // Flatten apps config array
        $this->config = [
            'raw' => $this->config,
            'flat' => $this->flattenConfig($this->config)
        ];

        // Check the loaded config against the keys of the default config
        // and set the default value if no cfg value is found
        foreach ($this->config['flat'] as $key => &$cfg_def) {

            $check_default = [
                'serialize' => false,
                'translate' => false,
                'data' => false,
                'validate' => [],
                'filter' => [],
                'default' => '',
                'control' => 'text'
            ];

            foreach ($check_default as $property => $default) {
                if (! isset($cfg_def[$property])) {
                    $cfg_def[$property] = $default;
                }
            }

            // When there is no config set but a default value defined for the app,
            // the default value will be used then
            if (! isset($this->cfg->data[$this->name][$key])) {
                $this->cfg->data[$this->name][$key] = $cfg_def['default'];
            }
        }
    }

    private function flattenConfig(array $array, $prefix = '', $glue = '.')
    {
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (isset($value['name'])) {
                    $result[$prefix . $value['name']] = $value;
                } else {
                    $result = $result + $this->flattenConfig($value, $prefix . $key . $glue);
                }
            } else {
                $result[$prefix . $key] = $value;
            }
        }
        return $result;
    }

    /**
     * Returns the namespace of the called component
     *
     * @return string
     */
    final public function getNamespace()
    {
        return substr(get_called_class(), 0, strrpos(get_called_class(), '\\'));
    }

    /**
     * Returns the path of app
     *
     * @return string
     */
    final public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns type (app or appsec) of app
     *
     * @return string
     */
    final public function getAppType()
    {
        // Normal app or secure app?
        return $this->secure === true ? 'appssec' : 'apps';
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
                $key = $this->stringUncamelize($file);

                $this->cfg->data[$this->name]['dir.' . $key] = $dir . '/' . $file;
                $this->cfg->data[$this->name]['url.' . $key] = $this->cfg->data['Core']['url.' . $this->getAppType()] . '/' . $this->name . '/' . $file;
            }
        }

        // Add apps base dir and url to app config
        $this->cfg->data[$this->name]['dir.app'] = $this->cfg->data['Core']['dir.' . $this->getAppType()] . '/' . $this->name;
        $this->cfg->data[$this->name]['url.app'] = $this->cfg->data['Core']['url.' . $this->getAppType()] . '/' . $this->name;

        // Cleanup
        closedir($handle);

        // App specific paths to add?
        if (method_exists($this, 'addPaths')) {
            $this->addPaths();
        }
    }

    /**
     * Initiates apps css
     *
     * Each app can have it's own css file. The css file needs to be placed in an Css folder within
     * the apps folder. App settings need a css flag, otherwise the css file won't be loaded.
     *
     * By default this method is first and only once called when a MVC object is requested on non AJAX requests.
     * In some cases the apps Css files has to be loaded even when no MVC object has been requested so far.
     * Use the app specifc Init() method to call initCss().
     *
     * @throws AppException
     *
     * @return \Core\Lib\Amvc\App
     */
    protected final function initCss()
    {
        // Init css only once
        if (self::$init_stages[$this->name]['css']) {
            return;
        }

        // Css flag set that indicates app has a css file?
        if ($this->css_file) {

            // Check for existance of apps css file
            if (! file_exists($this->cfg->data[$this->name]['dir.css'] . '/' . $this->name . '.css')) {
                Throw new AppException(sprintf('App "%s" css file does not exist. Either create the js file or remove the css flag in your app settings.', $this->name));
            }

            // Create css file link
            $this->page->css->link($this->cfg->data[$this->name]['url.css'] . '/' . $this->name . '.css');
        }

        // Set flag for initiated css
        self::$init_stages[$this->name]['css'] = true;

        return $this;
    }

    /**
     * Initiates apps javascript
     *
     * By default this method is first and only once called when a MVC object is requested on non AJAX requests.
     * In some cases the apps Js file has to be loaded even when no MVC object has been requested so far.
     * Use the app specifc Init() method to call initJs().
     *
     * @throws AppException
     *
     * @return \Core\Lib\Amvc\App
     */
    protected final function initJs()
    {
        // Init js only once
        if (self::$init_stages[$this->name]['js']) {
            return;
        }

        // Each app can (like css) have it's own javascript file. If you want to have this file included, you have to
        // set the public property $js in
        // your app mainclass. Unlike the css include procedure, the $js property holds also the information where to
        // include the apps .js file.
        // You hve to set this property to "scripts" (included on the bottom of website) or "header" (included in header
        // section of website).
        // the apps js file is stored within the app folder structure in an directory named "js".
        if ($this->js_file) {

            if (! file_exists($this->cfg->data[$this->name]['dir.js'] . '/' . $this->name . '.js')) {
                Throw new AppException(sprintf('App "%s" js file does not exist. Either create the js file or remove the js flag in your app mainclass.', $this->name));
            }

            $this->page->js->file($this->cfg->data[$this->name]['url.js'] . '/' . $this->name . '.js');
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
     */
    private final function initRoutes()
    {
        // Initiate routs only once regardless how many innstances of the app are created
        if (self::$init_stages[$this->name]['routes'] == true) {
            return;
        }

        if (! $this->routes) {

            // No routes set? Map at least index as default route
            $target = [
                'app' => $this->name,
                'controller' => 'Index',
                'action' => 'Index'
            ];

            $this->router->map('GET', '/', $target, $this->name . '_index');

            self::$init_stages[$this->name]['routes'] = true;

            return;
        }

        // Get uncamelized app name
        $app_name = $this->stringUncamelize($this->name);

        // Map routes to request handler router
        foreach ($this->routes as $def) {

            // Create route string
            $route = $def['route'] == '/' ? '/' . $app_name : '/' . (strpos($def['route'], '../') === false ? $app_name . $def['route'] : str_replace('../', '', $def['route']));

            // Create target
            $target = [
                // App not set means app will be set automatic.
                'app' => ! isset($def['app']) ? $app_name : $def['app']
            ];

            // is there a defined controller?
            if (! empty($def['controller'])) {
                $target['controller'] = $def['controller'];
            }

            if (! empty($def['action'])) {
                $target['action'] = $def['action'];
            }

            // The name of the route is set by the key in the routes array.
            // Is the name of type string it will be extended by the current
            // apps name.
            if (isset($def['name'])) {
                $name = (! isset($def['app']) ? $app_name : $def['app']) . '_' . $def['name'];
            }

            $method = isset($def['method']) ? $def['method'] : 'GET';

            $this->router->map($method, $route, $target, isset($name) ? $name : null);
        }

        self::$init_stages[$this->name]['routes'] = true;
    }

    /**
     * Is this app a secured one?
     *
     * @return boolean
     */
    final public function isSecure()
    {
        return $this->secure === true ? true : false;
    }

    /**
     * Returns the name of this app.
     *
     * @return string
     */
    final public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the apps id
     *
     * @return string
     */
    final public function getId()
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
     * Registers an app related service to di container.
     *
     * @param string $name
     *            Name of service
     * @param string $class
     *            Class name this service uses
     * @param array $args
     *            Optional arguments
     *
     * @return \Core\Lib\Amvc\App
     */
    final protected function registerService($name, $class, $args = [])
    {
        $this->di->mapService($this->name . '.' . $name, $class, $args);

        return $this;
    }

    /**
     * Registers an app related class factor to di container.
     *
     * @param string $name
     *            Name of factory
     * @param string $class
     *            Class name this service uses
     * @param array $args
     *            Optional arguments
     *
     * @return \Core\Lib\Amvc\App
     */
    final protected function registerFactory($name, $class, $args = [])
    {
        $this->di->mapFactory($this->name . '.' . $name, $class, $args);

        return $this;
    }

    /**
     * Registers an app related value to di container
     *
     * @param string $name
     *            Name of value
     * @param string $value
     *            The value itsel
     *
     * @return \Core\Lib\Amvc\App
     */
    final protected function registerValue($name, $value)
    {
        $this->di->mapValue($this->name . '.' . $name, $value);

        return $this;
    }
}
