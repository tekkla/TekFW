<?php
namespace Core\Lib;

use Core\Lib\Amvc\App;
use Core\Lib\Errors\Exceptions\InvalidArgumentException;
use Core\Lib\Errors\Exceptions\RuntimeException;

/**
 * DI.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class DI implements \ArrayAccess
{

    /**
     * Mapped services, factories and values
     *
     * @var array
     */
    private $map = [];

    /**
     * Singleton service storage
     *
     * @var array
     */
    private $names = [];

    /**
     * Constructor
     *
     * @param boolean $defaults Optional flag to map default services, factories and values. Default: true
     */
    public function __construct($defaults = true)
    {
        if ($defaults == true) {
            $this->mapDefaults();
        }
    }

    /**
     * Maps default services, factories and values
     */
    private function mapDefaults()
    {
        global $cfg;

        $this->mapValue('core.di', $this);

        // == DB ===========================================================
        $this->mapValue('db.default.driver', $cfg['db_driver']);
        $this->mapValue('db.default.host', $cfg['db_host']);
        $this->mapValue('db.default.port', $cfg['db_port']);
        $this->mapValue('db.default.name', $cfg['db_name']);
        $this->mapValue('db.default.user', $cfg['db_user']);
        $this->mapValue('db.default.pass', $cfg['db_pass']);
        $this->mapValue('db.default.options', [
            \PDO::ATTR_PERSISTENT => $cfg['db_persistent'],
            \PDO::ATTR_ERRMODE => $cfg['db_errmode'],
            \PDO::MYSQL_ATTR_INIT_COMMAND => $cfg['db_init_command']
        ]);
        $this->mapValue('db.default.prefix', $cfg['db_prefix']);
        $this->mapService('db.default.conn', '\Core\Lib\Data\Adapter\Db\Connection', [
            'db.default.name',
            'db.default.driver',
            'db.default.host',
            'db.default.port',
            'db.default.user',
            'db.default.pass',
            'db.default.options'
        ]);
        $this->mapFactory('db.default', '\Core\Lib\Data\DataAdapter', [
            'db',
            [
                'conn::db.default.conn',
                'prefix::db.default.prefix'
            ]
        ]);

        // == CONFIG =======================================================
        $this->mapService('core.cfg', '\Core\Lib\Cfg', 'db.default');

        // == HTTP =========================================================
        $this->mapService('core.http.router', '\Core\Lib\Http\Router');
        $this->mapService('core.http.session', '\Core\Lib\Http\Session', 'db.default');
        $this->mapFactory('core.http.cookie', '\Core\Lib\Http\Cookie');
        $this->mapService('core.http.post', '\Core\Lib\Http\Post', [
            'core.http.router',
            'core.sec.security'
        ]);

        // == UTILITIES ====================================================
        $this->mapFactory('core.util.timer', '\Core\Lib\Utilities\Timer');
        $this->mapFactory('core.util.time', '\Core\Lib\Utilities\Time');
        $this->mapFactory('core.util.shorturl', '\Core\Lib\Utilities\ShortenURL');
        $this->mapFactory('core.util.date', '\Core\Lib\Utilities\Date');
        $this->mapFactory('core.util.debug', '\Core\Lib\Utilities\Debug');
        $this->mapService('core.util.fire', '\FB');

        // == SECURITY =====================================================
        $this->mapService('core.sec.security', '\Core\Lib\Security\Security', [
            'db.default',
            'core.cfg',
            'core.http.session',
            'core.http.cookie',
            'core.sec.user.current',
            'core.sec.group',
            'core.sec.permission',
            'core.log'
        ]);
        $this->mapFactory('core.sec.user', '\Core\Lib\Security\User', [
            'db.default',
            'core.sec.permission'
        ]);
        $this->mapService('core.sec.user.current', '\Core\Lib\Security\User', [
            'db.default',
            'core.sec.permission'
        ]);
        $this->mapFactory('core.sec.inputfilter', '\Core\Lib\Security\Inputfilter');
        $this->mapService('core.sec.permission', '\Core\Lib\Security\Permission', 'db.default');
        $this->mapService('core.sec.group', '\Core\Lib\Security\Group', 'db.default');

        // == AMVC =========================================================
        $this->mapService('core.amvc.creator', '\Core\Lib\Amvc\Creator', 'core.cfg');
        $this->mapFactory('core.amvc.app', '\Core\Lib\Amvc\App');

        // == CACHE ========================================================
        $this->mapService('core.cache', '\Core\Lib\Cache\Cache');
        $this->mapFactory('core.cache.object', '\Core\Lib\Cache\CacheObject');

        // == IO ===========================================================
        $this->mapService('core.io.files', '\Core\Lib\IO\Files', [
            'core.log',
            'core.cfg'
        ]);
        $this->mapFactory('core.io.http', '\Core\Lib\IO\Http');

        // == LOGGING========================================================
        $this->mapService('core.log', '\Core\Lib\Logging\Logging', [
            'db.default',
            'core.http.session'
        ]);

        // == DATA ==========================================================
        $this->mapService('core.data.validator', '\Core\Lib\Data\Validator\Validator');
        $this->mapFactory('core.data.container', '\Core\Lib\Data\Container');
        $this->mapFactory('core.data.vars', '\Core\Lib\Data\Vars');

        // == CONTENT =======================================================
        $this->mapService('core.content', '\Core\Lib\Content\Content', [
            'core.http.router',
            'core.cfg',
            'core.amvc.creator',
            'core.content.html.factory',
            'core.content.nav',
            'core.content.css',
            'core.content.js',
            'core.content.message'
        ]);
        $this->mapService('core.content.lang', '\Core\Lib\Content\Language');
        $this->mapFactory('core.content.css', '\Core\Lib\Content\Css', [
            'core.cfg',
            'core.cache'
        ]);
        $this->mapFactory('core.content.js', '\Core\Lib\Content\Javascript', [
            'core.cfg',
            'core.http.router',
            'core.cache'
        ]);
        $this->mapService('core.content.message', '\Core\Lib\Content\Message', 'core.http.session');
        $this->mapService('core.content.nav', '\Core\Lib\Content\Menu');
        $this->mapFactory('core.content.menu', '\Core\Lib\Content\Menu');
        $this->mapService('core.content.html.factory', '\Core\Lib\Content\Html\HtmlFactory');

        // == AJAX ==========================================================
        $this->mapService('core.ajax', '\Core\Lib\Ajax\Ajax', 'core.content.message');

        // == ERROR =========================================================
        $this->mapService('core.error', '\Core\Lib\Errors\ExceptionHandler', [
            'core.http.router',
            'core.sec.user.current',
            'core.ajax',
            'core.content.message',
            'db.default',
            'core.cfg'
        ]);
    }

    /**
     * Creates an instance of a class
     *
     * Analyzes $arguments parameter and injects needed services and objects
     * into the object instance. A so created object instance gets always the
     * di container object injected.
     *
     * @param string $class_name
     * @param string $arguments
     *
     * @return object
     */
    public function instance($class_name, $arguments = null)
    {
        // Initialized the ReflectionClass
        $reflection = new \ReflectionClass($class_name);

        // Creating an instance of the class when no arguments provided
        if (empty($arguments) || count($arguments) == 0) {
            $obj = new $class_name();
        }

        // Creating instance of class with provided arguments
        else {

            if (! is_array($arguments)) {
                $arguments = (array) $arguments;
            }

            // Replace text arguments with objects
            foreach ($arguments as $key => $arg) {

                if (is_array($arg)) {

                    $options = [];

                    foreach ($arg as $arr_arg) {

                        list ($arg_key, $di_service) = explode('::', $arr_arg);

                        if (strpos($di_service, '.') === false) {
                            continue;
                        }

                        $options[$arg_key] = $this->get($di_service);
                    }

                    $arguments[$key] = $options;

                    continue;
                }

                // Skip strings without di container typical dot
                if (($arg instanceof App) || strpos($arg, '.') === false) {
                    continue;
                }

                $arguments[$key] = $this->get($arg);
            }

            $obj = $reflection->newInstanceArgs($arguments);
        }

        if (! property_exists($obj, 'di')) {
            $obj->di = $this;
        }

        // Inject and return the created instance
        return $obj;
    }

    /**
     * Maps a named value
     *
     * @param string $name Name of the value
     * @param unknown $value The value itself
     */
    public function mapValue($name, $value)
    {
        $this->map[$name] = [
            'value' => $value,
            'type' => 'value'
        ];
    }

    /**
     * Maps a named service.
     *
     * Requesting this service will result in returning always the same object.
     *
     * @param string $name Name of the service
     * @param string $value Class to use for object creation
     * @param string $arguments Arguments to provide on instance create
     */
    public function mapService($name, $value, $arguments = null)
    {
        $this->map[$name] = [
            'value' => $value,
            'type' => 'service',
            'arguments' => $arguments
        ];
    }

    /**
     * Maps a class by name.
     *
     * Requestingthis class will result in new object.
     *
     * @param string $name Name to access object
     * @param string $value Classname of object
     * @param string $arguments Arguments to provide on instance create
     */
    public function mapFactory($name, $value, $arguments = null)
    {
        $this->map[$name] = [
            'value' => $value,
            'type' => 'factory',
            'arguments' => $arguments
        ];
    }

    /**
     * Executes object method by using Reflection
     *
     * @param $obj Object to call parameter injected method
     * @param $method Name of method to call
     * @param $params (Optional) Array of parameters to inject into method
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     *
     * @return object
     */
    public function invokeMethod(&$obj, $method, array $params = [])
    {
        if (! is_array($params)) {
            Throw new InvalidArgumentException('Parameter to invoke needs to be of type array.');
        }

        // Look for the method in object. Throw error when missing.
        if (! method_exists($obj, $method)) {
            Throw new InvalidArgumentException(sprintf('Method "%s" not found.', $method), 5000);
        }

        // Get reflection method
        $method = new \ReflectionMethod($obj, $method);

        // Init empty arguments array
        $args = [];

        // Get list of parameters from reflection method object
        $method_parameter = $method->getParameters();

        // Let's see what arguments are needed and which are optional
        foreach ($method_parameter as $parameter) {

            // Get current paramobject name
            $param_name = $parameter->getName();

            // Parameter is not optional and not set => throw error
            if (! $parameter->isOptional() && ! isset($params[$param_name])) {
                Throw new RuntimeException(sprintf('Not optional parameter "%s" missing', $param_name), 2001);
            }

            // If parameter is optional and not set, set argument to null
            $args[] = $parameter->isOptional() && ! isset($params[$param_name]) ? null : $params[$param_name];
        }

        // Return result executed method
        return $method->invokeArgs($obj, $args);
    }

    /**
     * Returns the requested SFV (Service/Factory/Value).
     *
     * @param string $name Name of the Service, Factory or Value to return
     *
     * @throws InvalidArgumentException
     *
     * @return unknown|Ambigous
     */
    private function getSFV($name)
    {
        if (! $this->offsetExists($name)) {
            Throw new InvalidArgumentException(sprintf('Service, factory or value "%s" is not mapped.', $name));
        }

        $type = $this->map[$name]['type'];
        $value = $this->map[$name]['value'];

        if ($type == 'value') {
            return $value;
        }
        elseif ($type == 'factory') {
            return $this->instance($value, $this->map[$name]['arguments']);
        }
        else {

            if (! isset($this->services[$name])) {
                $this->services[$name] = $this->instance($value, $this->map[$name]['arguments']);
            }

            return $this->services[$name];
        }
    }

    /**
     * Checks for a registred SFV.
     *
     * @param string $name Name of service, factory or value to check
     *
     * @return boolean
     */
    public function exists($name)
    {
        return $this->offsetExists($name);
    }

    /**
     * Returns requested service, factory or value
     *
     * @param string $name Name of registered service, class or value
     */
    public function get($name)
    {
        return $this->getSFV($name);
    }

    public function log($var)
    {
        $this->get('core.util.fire')->log($var);
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($name)
    {
        return array_key_exists($name, $this->map);
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($name)
    {
        return $this->getSFV($name);
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetSet()
     *
     * @throws InvalidArgumentException
     */
    public function offsetSet($name, $value)
    {
        // No mapping through this way.
        Throw new InvalidArgumentException('It is not allowed to map services, factories or values this way. Use the specific map methods instead.');
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($name)
    {
        if ($this->offsetExists($name)) {
            unset($this->map[$name]);
        }
    }
}
