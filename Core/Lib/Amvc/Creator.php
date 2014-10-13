<?php
namespace Core\Lib\Amvc;

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
	 *
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

			// Create an app instance
			$app = self::$instances[$name] = $this->di->instance($class, $args);

			// And init the app
			$app->init();
		}

		// Return app instance
		return self::$instances[$name];
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
						if ($name == '..' || $name == '.' || $name == 'Core') {
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
