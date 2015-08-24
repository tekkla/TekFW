<?php
namespace Core\Lib\Amvc;

use Core\Lib\Cfg;

/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 */
class Creator
{

    /**
     * List of secured app, which resides within the framework folder.
     *
     * @var array
     */
    private $secure_apps = [
        'Admin',
        'Doc',
        'Core'
    ];

    /**
     * List of apps, which can get instances of secured apps.
     *
     * @var unknown
     */
    private $allow_secure_instance = [
        'Admin',
        'Doc',
        'Core'
    ];

    private $instances = [];

    /**
     *
     * @var Cfg
     */
    private $cfg;

    /**
     * Make this class defintive a singleton
     */
    public function __construct(Cfg $cfg)
    {
        $this->cfg = $cfg;
    }

    /**
     * Get a singleton app object
     *
     * @param string $name
     * @param bool $do_init
     *
     * @return App
     */
    public function &getAppInstance($name)
    {
        if (empty($name)) {
            $this->send404();
        }

        // Check for already existing instance of app
        // and create new instance when none is found
        if (! array_key_exists($name, $this->instances)) {

            // Create app namespace and take care of secured apps.
            $class = in_array($name, $this->secure_apps) ? '\Core\AppsSec\\' . $name . '\\' . $name : '\Apps\\' . $name . '\\' . $name;

            $filename = BASEDIR . str_replace('\\', '/', $class) . '.php';

            if (! file_exists($filename)) {
                $this->send404();
            }

            // Default arguments for each app instance
            $args = [
                $name,
                'core.cfg',
                'core.http.router',
                'core.content',
                'core.sec.permission',
                'core.sec.security',
                'core.di'
            ];

            // Create an app instance
            $this->instances[$name] = $this->di->instance($class, $args);
        }

        // Return app instance
        return $this->instances[$name];
    }

    private function send404()
    {
        header("HTTP/1.0 404 Page not found.");
        echo '<h1>404 - Not Found</h1><p>The requsted page does not exists.</p><p><a href="/">Goto to Homepage?<a></p>';
        error_log('AMVC Creator Error: App class was not found.' . PHP_EOL . print_r(debug_backtrace(null,10), true));
        exit();
    }

    /**
     * Autodiscovers installed apps in the given path.
     * When an app is found an instance of it will be created.
     *
     * @param string|array $path Path to check for apps. Can be an array of paths.
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
                        $this->getAppInstance($name);
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
     * Inits configuration of an app
     *
     * Uuses both config values stroed in db and adds default values from app
     * config definition for missing config values.
     *
     * @param string $app_name
     */
    public function initAppConfig($app_name)
    {
        // Init app
        $cfg_app = $this->getAppInstance($app_name)->getConfig();

        // Add default values for not set config
        foreach ($cfg_app as $key => $cfg_def) {

            // Set possible vaue from apps default config when no config was loaded from db
            if (! $this->cfg->exists($app_name, $key) && isset($cfg_def['default'])) {
                $this->cfg->set($app_name, $key, $cfg_def['default']);
            }
        }
    }

    /**
     * Returns a list of loaded app names
     *
     * @return array
     */
    public function getLoadedApps()
    {
        return array_keys($this->instances);
    }
}
