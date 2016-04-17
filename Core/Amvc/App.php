<?php
namespace Core\Amvc;

use Core\DI;
use Core\Cfg\Cfg;
use Core\Security\Security;
use Core\Security\Permission;
use Core\Page\Page;
use Core\Router\Router;
use Core\IO\IO;
use Core\Language\Language;
use Core\Router\UrlTrait;
use Core\Traits\StringTrait;
use Core\Language\TextTrait;
use Core\Cfg\CfgTrait;

/**
 * App.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class App
{

    use TextTrait;
    use CfgTrait;
    use UrlTrait;
    use StringTrait;

    const LANGUAGE = 'language';
    const SECURE = 'secure';
    const CSS = 'css';
    const JS = 'js';

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
     * App option flags array
     *
     * @var array
     */
    protected $flags = [];

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
     * @var IO
     */
    protected $io;

    /**
     *
     * @var Language
     */
    protected $language;

    /**
     *
     * @var DI
     */
    public $di;

    /**
     *
     * @var Creator
     */
    public $creator;

    final public function __construct($app_name, Cfg $cfg, Router $router, Page $page, Security $security, IO $io, Language $language, Creator $creator, DI $di)
    {
        // Setting properties
        $this->name = $app_name;
        $this->cfg = $cfg;
        $this->router = $router;
        $this->page = $page;
        $this->security = $security;
        $this->io = $io;
        $this->language = $language;
        $this->creator = $creator;
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

        // Call possible load method
        if (method_exists($this, 'Load')) {
            $this->Load();
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

        // Init page
        $this->page->init();
    }

    /**
     * Inits apps permissions by addind default values for admin and for config if confix exists
     */
    final private function initPermissions()
    {
        // Init only once
        if (self::$init_stages[$this->name]['perms']) {
            return;
        }

        // Add admin permission by default
        $default = [
            'admin'
        ];

        // Having a config means we have to add an admin config permission
        if ($this->config) {
            $default[] = 'config';
        }

        // Put all default perms in front of all other perms
        foreach ($default as $perm) {
            array_unshift($this->permissions, $perm);
        }

        // Send permissions to Permission service
        $this->security->permission->setPermissions($this->name, $this->permissions);

        // Set flat that permission init is done
        self::$init_stages[$this->name]['perms'] = true;
    }

    /**
     * Inits the language file according to the current language the site/user uses
     *
     * @throws AppException
     */
    final private function initLanguage()
    {

        // Init only once
        if (! empty(self::$init_stages[$this->name]['lang'])) {
            return;
        }

        // Do we have permissions do add?
        if (in_array(self::LANGUAGE, $this->flags)) {

            // Check
            if (empty($this->cfg->data[$this->name]['dir.language'])) {
                Throw new AppException(sprintf('Languagefile of app "%s" has to be loaded but no Language folder was found.', $this->name));
            }

            // Always load english language files.
            $language_file = $this->cfg->data[$this->name]['dir.language'] . '/en.php';
            $this->language->loadLanguageFile($this->name, $language_file);

            // After that load set languenage file which can override the loaded english string.
            $default_language = $this->cfg->data['Core']['site.language.default'];

            if ($default_language != 'en') {

                $language_file = $this->cfg->data[$this->name]['dir.language'] . '/' . $default_language . '.php';
                $this->language->loadLanguageFile($this->name, $language_file);
            }
        }

        self::$init_stages[$this->name]['language'] = true;
    }

    /**
     * Hidden method to factory mvc components like models, views or controllers
     *
     * @param string $name
     *            Components name
     * @param string $type
     *            Components type
     *
     * @return Model|View|Controller
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

        if (! $this->io->files->checkClassFileExists($class)) {
            return false;
        }

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
     *
     * @return Model
     */
    final public function getModel($name = '')
    {
        if (empty($name)) {
            $name = $this->getComponentsName();
        }

        $name = $this->stringCamelize($name);
        $args = [
            'core.security'
        ];

        return $this->MVCFactory($name, 'Model', $args);
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

        $name = $this->stringCamelize($name);
        $args = [
            'core.router',
            'core.http',
            'core.security',
            'core.page',
            'core.html.factory',
            'core.ajax',
            'core.io'
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

        $name = $this->stringCamelize($name);

        return $this->MVCFactory($name, 'View');
    }

    /**
     * Returns apps default config
     *
     * @return array
     */
    final public function getConfig($refresh = false)
    {
        if ($refresh) {
            $this->initCfg();
        }

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
        $this->cfg->addDefinition($this->name, $this->config);
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
        return in_array(self::SECURE, $this->flags) ? 'appssec' : 'apps';
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
     * @return \Core\Amvc\App
     */
    protected final function initCss()
    {
        // Init css only once
        if (self::$init_stages[$this->name]['css']) {
            return;
        }

        // Css flag set that indicates app has a css file?
        if (in_array(self::CSS, $this->flags)) {

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
     * @return \Core\Amvc\App
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
        if (in_array(self::JS, $this->flags)) {

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

        $this->router->mapAppRoutes($this->name, $this->routes);

        self::$init_stages[$this->name]['routes'] = true;
    }

    /**
     * Is this app a secured one?
     *
     * @return boolean
     */
    final public function isSecure()
    {
        return in_array(self::SECURE, $this->flags);
    }

    /**
     * Returns the name of this app.
     *
     * @return string
     */
    final public function getName($uncamelize = false)
    {
        return $uncamelize == true ? $this->stringUncamelize($this->name) : $this->name;
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
     * Returns the apps permissions
     *
     * @return array
     */
    final public function getPermissions()
    {
        return $this->permissions;
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
     * @return \Core\Amvc\App
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
     * @return \Core\Amvc\App
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
     * @return \Core\Amvc\App
     */
    final protected function registerValue($name, $value)
    {
        $this->di->mapValue($this->name . '.' . $name, $value);

        return $this;
    }
}
