<?php
namespace Core;

use Core\Errors\CoreException;

/**
 * DI.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class DI implements \ArrayAccess
{

    /**
     *
     * @var array
     */
    private $settings = [];

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

    private static $instance = false;

    /**
     * Creates and returns singleton instance of DI service
     *
     * @param array $settings
     *            Optional settings array which is only needed once on instance creation
     *
     * @return \Core\DI
     */
    public static function getInstance(array $settings = [])
    {
        if (! self::$instance) {
            self::$instance = new DI($settings);
        }

        return self::$instance;
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
                if (($arg instanceof \Core\Amvc\App) || strpos($arg, '.') === false) {
                    continue;
                }

                $arguments[$key] = $this->get($arg);
            }

            $obj = $reflection->newInstanceArgs($arguments);
        }

        // if (! property_exists($obj, 'di')) {
        $obj->di = $this;
        // }

        // Inject and return the created instance
        return $obj;
    }

    /**
     * Maps a named value
     *
     * @param string $name
     *            Name of the value
     * @param unknown $value
     *            The value itself
     */
    public function mapValue($name, $value)
    {
        $this->map[$name] = [
            'value' => $value,
            'type' => 'value'
        ];
    }

    /**
     * Maps a named service
     *
     * Requesting this service will result in returning always the same object.
     *
     * @param string $name
     *            Name of the service
     * @param string $value
     *            Class to use for object creation
     * @param string $arguments
     *            Arguments to provide on instance create
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
     * Maps a class by name
     *
     * Requestingthis class will result in new object.
     *
     * @param string $name
     *            Name to access object
     * @param string $value
     *            Classname of object
     * @param string $arguments
     *            Arguments to provide on instance create
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
     * @param $obj Object
     *            to call parameter injected method
     * @param $method Name
     *            of method to call
     * @param $params (Optional)
     *            Array of parameters to inject into method
     *
     * @throws CoreException
     *
     * @return object
     */
    public function invokeMethod(&$obj, $method, array $params = [])
    {
        if (! is_array($params)) {
            Throw new CoreException('Parameter to invoke needs to be of type array.');
        }

        // Look for the method in object. Throw error when missing.
        if (! method_exists($obj, $method)) {
            Throw new CoreException(sprintf('Method "%s" not found.', $method), 5000);
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
                Throw new CoreException(sprintf('Not optional parameter "%s" missing', $param_name), 2001);
            }

            // If parameter is optional and not set, set argument to null
            $args[] = $parameter->isOptional() && ! isset($params[$param_name]) ? null : $params[$param_name];
        }

        // Return result executed method
        return $method->invokeArgs($obj, $args);
    }

    /**
     * Returns the requested SFV (Service/Factory/Value)
     *
     * @param string $name
     *            Name of the Service, Factory or Value to return
     *
     * @throws CoreException
     *
     * @return unknown|Ambigous
     */
    private function getSFV($name)
    {
        if (! $this->offsetExists($name)) {
            Throw new CoreException(sprintf('Service, factory or value "%s" is not mapped.', $name));
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
     * Checks for a registred SFV
     *
     * @param string $name
     *            Name of service, factory or value to check
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
     * @param string $name
     *            Name of registered service, class or value
     */
    public function get($name)
    {
        return $this->getSFV($name);
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
     * @throws CoreException
     */
    public function offsetSet($name, $value)
    {
        // No mapping through this way.
        Throw new CoreException('It is not allowed to map services, factories or values this way. Use the specific map methods instead.');
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
