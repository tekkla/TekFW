<?php
namespace Core\Amvc;

use Core\Cfg\Cfg;
use Core\Traits\StringTrait;

/**
 * Creator.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Creator
{

    use StringTrait;

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
     * @return \Core\Amvc\App
     */
    public function &getAppInstance($name)
    {
        if (empty($name)) {
            Throw new AmvcException('Amvc creators getAppInstance() method needs an app name.');
        }

        $name = $this->stringCamelize($name);

        // App instances are singletons!
        if (array_key_exists($name, $this->instances)) {
            return $this->instances[$name];
        }

        // New app instance Create app namespace and take care of secured apps.
        $class = in_array($name, $this->secure_apps) ? '\AppsSec\\' . $name . '\\' . $name : '\Apps\\' . $name . '\\' . $name;

        $filename = BASEDIR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

        if (! file_exists($filename)) {
            Throw new AmvcException(sprintf('Creator could not find an app classfile "%s" for app "%s"', $name, $filename));
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

                        if ($name{0} == '.' || $name == 'Core' || is_file($apps_dir . '/' . $name)) {
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
