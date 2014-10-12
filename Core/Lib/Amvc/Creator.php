<?php
namespace Core\Lib\Amvc;

/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
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

    private static $instances = [];

    /**
     * Make this class defintive a singleton
     */
    protected function __constructor()
    {}

    private function __clone()
    {}

    private function __wakeup()
    {}

    /**
     * Get an unique app object
     *
     * @param string $name
     * @return App
     */
    public function getAppInstance($name, $do_init = false)
    {
        if (! is_bool($do_init)) {
            Throw new \InvalidArgumentException('Init flag for apps have to be of type boolean');
        }

        // Make sure to have an app instance already
        if (! isset(self::$instances[$name])) {

            // Create new app instance
            $this->create($name);

            // Creation already did initiation
            $do_init = false;
        }

        // Get clone of app
        $app = clone self::$instances[$name];

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
     * @return App
     */
    public function &create($name)
    {
        // Create app namespace and take care of secured apps.
        $class = in_array($name, $this->secure_apps) ? '\Core\AppsSec\\' . $name . '\\' . $name : '\Apps\\' . $name . '\\' . $name;

        // Check for already existing instance of app
        // and create new instance when none is found
        if (! array_key_exists($name, self::$instances)) {
            // Default arguments for each app instance
            $args = [
                $name,
                'core.cfg',
                'core.request',
                'core.content.css',
                'core.content.js',
            	'core.content.nav'
            ];

            $app = self::$instances[$name] = $this->di->instance($class, $args);
            $app->init();
        }

        // Return app instance
        return self::$instances[$name];
    }

    public function autodiscover($dirs)
    {
        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                if (($dh = opendir($dir)) !== false) {
                    while (($file = readdir($dh)) !== false) {
                        if ($file == '..' || $file == '.' || $file == 'Core') {
                            continue;
                        }

                        $this->create($file);
                    }
                    closedir($dh);
                }
            }
        }
    }

    public function initAppConfig($app_name)
    {
        // Init app
        $cfg_app = $this->create($app_name)->getSettings();

        // Get global config
        $cfg = $this->di['core.cfg'];

        // Add default values for not set config
        foreach ($cfg_app as $key => $cfg_def) {
            // Set possible vaue from apps default config when no config was loaded from db
            if (! $cfg->exists($app_name, $key) && isset($cfg_def['default']))
                $cfg->set($app_name, $key, $cfg_def['default']);
        }
    }
}
