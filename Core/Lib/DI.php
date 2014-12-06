<?php
namespace Core\Lib;

use Core\Lib\Amvc\App;

/**
 *  DI Container
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license MIT
 *
 */
class DI implements \ArrayAccess
{

    private $map = [];

    private $services = [];

    public function __construct($defaults = true)
    {
        if ($defaults == true)
            $this->mapDefaults();
    }

    /**
     * Maps some core default services, factories and values
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
            \PDO::ATTR_PERSISTENT => true,
            \PDO::ATTR_ERRMODE => 2,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
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

        // == ERROR=========================================================
        $this->mapFactory('core.error', '\Core\Lib\Error\Error');

        // == HTTP =========================================================
        $this->mapService('core.http.router', '\Core\Lib\Http\Router');
        $this->mapService('core.http.post', '\Core\Lib\Http\Post', 'core.http.router');
        $this->mapService('core.http.session', '\Core\Lib\Http\Session', 'db.default');
        $this->mapFactory('core.http.cookie', '\Core\Lib\Http\Cookie');

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
            'core.sec.permission'
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

        // == IO ===========================================================
        $this->mapFactory('core.io.file', '\Core\Lib\IO\File');
        $this->mapFactory('core.io.http', '\Core\Lib\IO\Http');

        // == DATA ==========================================================
        $this->mapFactory('core.data.validator', '\Core\Lib\Data\Validator\Validator');
        $this->mapFactory('core.data.container', '\Core\Lib\Data\Container');

        // == CONTENT =======================================================
        $this->mapService('core.content.content', '\Core\Lib\Content\Content', [
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
        $this->mapFactory('core.content.css', '\Core\Lib\Content\Css', 'core.cfg');
        $this->mapFactory('core.content.js', '\Core\Lib\Content\Javascript', [
            'core.cfg',
            'core.http.router'
        ]);
        $this->mapFactory('core.content.message', '\Core\Lib\Content\Message', 'core.http.session');
        $this->mapFactory('core.content.url', '\Core\Lib\Content\Url', 'core.http.router');
        $this->mapService('core.content.nav', '\Core\Lib\Content\Menu');
        $this->mapFactory('core.content.menu', '\Core\Lib\Content\Menu');
        $this->mapService('core.content.html.factory', '\Core\Lib\Content\Html\HtmlFactory');

        // == AJAX ==========================================================
        $this->mapService('core.ajax', '\Core\Lib\Ajax\Ajax');
        $this->mapFactory('core.ajax.cmd', '\Core\Lib\Ajax\AjaxCommand');

        // == ERROR =========================================================
        $this->mapService('core.error', '\Core\Lib\Errors\Error', [
            'core.http.router',
            'core.sec.user.current',
            'core.ajax',
            'core.content.message',
            'db.default'
        ]);
    }

    /**
     * Creates an instance of a class
     *
     * Analyzes $arguments parameter and injects needed services and objects
     * into the object instance. A so created object instance gets always the
     * di container object injected.
     *
     * @param unknown $class_name
     * @param string $arguments
     *
     * @return object
     */
    public function instance($class_name, $arguments = null)
    {
        // Initialized the ReflectionClass
        $reflection = new \ReflectionClass($class_name);

        // Creating an instance of the class when no arguments provided
        if ($arguments === null || count($arguments) == 0) {
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
     * @param string $key
     *            Name of the value
     * @param unknown $value
     *            The value itself
     */
    public function mapValue($key, $value)
    {
        $this->map[$key] = [
            'value' => $value,
            'type' => 'value'
        ];
    }

    /**
     * Maps a named service.
     *
     * Requesting this service will result in returning always the same object.
     *
     * @param string $key
     *            Name of the service
     * @param string $value
     *            Class to use for object creation
     * @param string $arguments
     *            Arguments to provide on instance create
     */
    public function mapService($key, $value, $arguments = null)
    {
        $this->map[$key] = [
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
     * @param string $key
     *            Name to access object
     * @param string $value
     *            Classname of object
     * @param string $arguments
     *            Arguments to provide on instance create
     */
    public function mapFactory($key, $value, $arguments = null)
    {
        $this->map[$key] = [
            'value' => $value,
            'type' => 'factory',
            'arguments' => $arguments
        ];
    }

    /**
     * Executes object method by using Reflection
     *
     * @param $obj Object
     *            to call parameter injected method
     * @param $method Name
     *            of method to call
     * @param $params (Optional)
     *            Array of parameters to inject into method
     *
     * @throws MethodNotExistsError
     * @throws ParameterNotSetError
     *
     * @return object
     */
    public function invokeMethod(&$obj, $method, array $params = [])
    {
        if (! is_array($params)) {
            Throw new \InvalidArgumentException('Parameter to invoke needs to be of type array.');
        }

        // Look for the method in object. Throw error when missing.
        if (! method_exists($obj, $method)) {
            Throw new \InvalidArgumentException(sprintf('Method "%s" not found.', $method), 5000);
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
                Throw new \RuntimeException(sprintf('Not optional parameter "%s" missing', $param_name), 2001);
            }

            // If parameter is optional and not set, set argument to null
            $args[] = $parameter->isOptional() && ! isset($params[$param_name]) ? null : $params[$param_name];
        }

        // Return result executed method
        return $method->invokeArgs($obj, $args);
    }

    /**
     * Checks for a registred service by it's name.
     *
     * @param string $service
     *            Name of service to check for
     *
     * @return boolean
     */
    public function exists($service)
    {
        return $this->offsetExists($service);
    }

    /**
     * Returns requested service, class or value
     *
     * @param string $service
     *            Name of registered service, class or value
     */
    public function get($service)
    {
        return $this->offsetGet($service);
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($service)
    {
        return array_key_exists($service, $this->map);
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($service)
    {
        if (! $this->offsetExists($service)) {
            Throw new \InvalidArgumentException(sprintf('Service, factory or value "%s" is not mapped.', $service));
        }

        $type = $this->map[$service]['type'];
        $value = $this->map[$service]['value'];

        if ($type == 'value') {
            return $value;
        } elseif ($type == 'factory') {
            return $this->instance($value, $this->map[$service]['arguments']);
        } else {

            if (! isset($this->services[$service])) {
                $this->services[$service] = $this->instance($value, $this->map[$service]['arguments']);
            }

            return $this->services[$service];
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($service, $value)
    {
        $this->map[$service] = $value;
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($service)
    {
        if ($this->offsetExists($service)) {
            unset($this->map[$service]);
        }
    }
}
