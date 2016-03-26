<?php
namespace Core\Lib\Amvc;

use Core\Lib\Cfg\Cfg;

/**
 * Creator.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Creator
{

    /**
     *
     * @var array
     */
    private $secure_apps = [
        'Core'
    ];

    /**
     *
     * @var Array
     */
    private $allow_secure_instance = [
        'Core'
    ];

    /**
     *
     * @var array
     */
    private $instances = [];

    /**
     *
     * @var Cfg
     */
    private $cfg;

    /**
     * Constructor
     *
     * @param Cfg $cfg
     *            Cfg dependency
     */
    public function __construct(Cfg $cfg)
    {
        $this->cfg = $cfg;
    }

    /**
     * Get a singleton app object
     *
     * @param string $name
     *            Name of app instance to get
     *
     * @return \core\Lib\Amvc\App
     */
    public function &getAppInstance($name)
    {
        if (empty($name)) {
            $this->send404();
        }

        // App instances are singletons!
        if (array_key_exists($name, $this->instances)) {
            return $this->instances[$name];
        }

        // New app instance Create app namespace and take care of secured apps.
        $class = in_array($name, $this->secure_apps) ? '\Core\AppsSec\\' . $name . '\\' . $name : '\Apps\\' . $name . '\\' . $name;

        $filename = BASEDIR . str_replace('\\', '/', $class) . '.php';

        if (! file_exists($filename)) {
            $this->send404($filename);
        }

        // Default arguments for each app instance
        $args = [
            $name,
            'core.cfg',
            'core.router',
            'core.page',
            'core.security',
            'core.io',
            'core.language',
            'core.amvc.creator',
            'core.di'
        ];

        $instance = $this->di->instance($class, $args);

        $this->instances[$name] = $instance;

        return $instance;
    }

    private function send404($filename)
    {
        error_log(sprintf('AMVC Creator Error: App class "%s" was not found.', $filename));

        header("HTTP/1.0 404 Page not found.");
        echo '<h1>404 - Not Found</h1><p>The requested page does not exists.</p><p><a href="/">Goto to Homepage?</a></p>';
        echo '<hr>';
        echo '<small>Missing: ', $filename, '</small>';

        exit();
    }

    /**
     * Autodiscovers installed apps in the given path
     *
     * When an app is found an instance of it will be created.
     *
     * @param string|array $path
     *            Path to check for apps. Can be an array of paths
     */
    public function autodiscover($path)
    {
        if (! is_array($path)) {
            $path = (array) $path;
        }

        foreach ($path as $apps_dir) {

            if (is_dir($apps_dir)) {
                if (($dh = opendir($apps_dir)) !== false) {

                    while (($name = readdir($dh)) !== false) {

                        if ($name{0} == '.' || $name == 'Core' || is_file($name)) {
                            continue;
                        }

                        $app = $this->getAppInstance($name);
                    }

                    closedir($dh);
                }
            }
        }

        // Call Start() event after all app instances have been loaded
        foreach ($this->instances as $app) {
            if (method_exists($app, 'Start')) {
                $app->Start();
            }
        }
    }

    /**
     * Returns a list of loaded app names
     *
     * @param boolean $only_names
     *            Optional flag to switch the return value to be only an array of app names or instances (Default: true)
     *
     * @return array
     */
    public function getLoadedApps($only_names = true)
    {
        if ($only_names) {
            return array_keys($this->instances);
        }

        return $this->instances;
    }
}
