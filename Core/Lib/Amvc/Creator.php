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
     * Get an unique app object by cloning an app instance
     *
     * @param string $name
     *
     * @return App
     */
    public function getAppInstance($name, $do_init = false)
    {
        if (! is_bool($do_init)) {
            Throw new \InvalidArgumentException('Init flag for apps have to be of type boolean');
        }

        // Make sure to have an app instance already
        if (! isset($this->instances[$name])) {

            // Create new app instance
            $this->create($name);

            // Creation already did initiation
            $do_init = false;
        }

        // Get clone of app
        $app = clone $this->instances[$name];

        // Init this app instance
        if ($do_init) {
            $app->init();
        }

        // Return referenc to app object in instance storage
        return $app;
    }

    /**
     * Get a singleton app object
     *
     * @param string $name
     * @param bool $do_init
     *
     * @return App
     */
    public function &create($name)
    {
        // Create app namespace and take care of secured apps.
        $class = in_array($name, $this->secure_apps) ? '\Core\AppsSec\\' . $name . '\\' . $name : '\Apps\\' . $name . '\\' . $name;

        $filename = BASEDIR . str_replace('\\', '/', $class) . '.php';

        if (!file_exists($filename)) {
            throw new \RuntimeException('AMVC Creator Error: App class file "' . $filename . '" was not found.');
        }

        // Check for already existing instance of app
        // and create new instance when none is found
        if (! array_key_exists($name, $this->instances)) {

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
            $app = $this->instances[$name] = $this->di->instance($class, $args);
        }

        // Return app instance
        return $this->instances[$name];
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
                        $this->create($name);
                    }

                    closedir($dh);
                }
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
        $cfg_app = $this->create($app_name)->getConfig();

        // Add default values for not set config
        foreach ($cfg_app as $key => $cfg_def) {

            // Set possible vaue from apps default config when no config was loaded from db
            if (! $this->cfg->exists($app_name, $key) && isset($cfg_def['default']))
                $this->cfg->set($app_name, $key, $cfg_def['default']);
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
