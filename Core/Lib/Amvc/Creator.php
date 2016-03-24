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
     * List of secured app, which resides within the framework folder.
     *
     * @var array
     */
    private $secure_apps = [
        'Core'
    ];

    /**
     * List of apps, which can get instances of secured apps.
     *
     * @var Array
     */
    private $allow_secure_instance = [
        'Core'
    ];

    /**
     * Storage for app instances
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

        // Check for already existing instance of app and return reference to app if is
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

        // Create an app instance
        $this->instances[$name] = $this->di->instance($class, $args);

        // Return app instance
        return $this->instances[$name];
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
     * Autodiscovers installed apps in the given path.
     * When an app is found an instance of it will be created.
     *
     * @param string|array $path
     *            Path to check for apps. Can be an array of paths.
     */
    public function autodiscover($path)
    {
        if (! is_array($path)) {
            $path = (array) $path;
        }

        foreach ($path as $apps_dir) {

            // Dir found?
            if (is_dir($apps_dir)) {

                // Try to open apps dir
                if (($dh = opendir($apps_dir)) !== false) {

                    // Check each dir member for apps
                    while (($name = readdir($dh)) !== false) {

                        // Skip Core app and parent names
                        if ($name == '..' || $name == '.' || $name == 'Core' || substr($name, 0, 1) == '.') {
                            continue;
                        }

                        // Create app by using the current dirs name as app name
                        $app = $this->getAppInstance($name);
                    }

                    closedir($dh);
                }
            }
        }
        // Run possible Start() method in apps
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
