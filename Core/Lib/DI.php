<?php
namespace Core\Lib;

use Core\Lib\Amvc\App;

/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 *
 */
class DI implements \ArrayAccess
{

	private static $map = [];

	private static $services = [];

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

				// Skip strings without di container typical dot
				if (($arg instanceof App) || strpos($arg, '.') === false) {
					continue;
				}

				$arguments[$key] = $this[$arg];
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
	 * @param string $key Name of the value
	 * @param unknown $value The value itself
	 */
	public function mapValue($key, $value)
	{
		self::$map[$key] = [
			'value' => $value,
			'type' => 'value'
		];
	}

	/**
	 * Maps a named service.
	 *
	 * Requesting this service will result in returning always the same object.
	 *
	 * @param string $key Name of the service
	 * @param string $value Class to use for object creation
	 * @param string $arguments Arguments to provide on instance create
	 */
	public function mapService($key, $value, $arguments = null)
	{
		self::$map[$key] = [
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
	 * @param string $key Name to access object
	 * @param string $value Classname of object
	 * @param string $arguments Arguments to provide on instance create
	 */
	public function mapFactory($key, $value, $arguments = null)
	{
		self::$map[$key] = [
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
	 * @param $param (Optional) Array of parameters to inject into method
	 *
	 * @throws MethodNotExistsError
	 * @throws ParameterNotSetError
	 *
	 * @return object
	 */
	public function invokeMethod(&$obj, $method, $param = [])
	{
		if (! is_array($param)) {
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
			if (! $parameter->isOptional() && ! isset($param[$param_name])) {
				Throw new \RuntimeException(sprintf('Not optional parameter "%s" missing', $param_name), 2001);
			}

			// If parameter is optional and not set, set argument to null
			$args[] = $parameter->isOptional() && ! isset($param[$param_name]) ? null : $param[$param_name];
		}

		// Return result executed method
		return $method->invokeArgs($obj, $args);
	}

	/**
	 * Checks for a registred service by it's name.
	 *
	 * @param string $service Name of service to check for
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
	 * @param string $service Name of registered service, class or value
	 */
	public function get($service) {
		$this->offsetGet($service);
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($service)
	{
		return array_key_exists($service, self::$map);
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($service)
	{
		if (! $this->offsetExists($service)) {
			Throw new \InvalidArgumentException(sprintf('Service, factory or value "%s" is not mapped.', $service));
		}

		$type = self::$map[$service]['type'];
		$value = self::$map[$service]['value'];

		if ($type == 'value') {
			return $value;
		} elseif ($type == 'factory') {
			return $this->instance($value, self::$map[$service]['arguments']);
		} else {

			if (! isset(self::$services[$service])) {
				self::$services[$service] = $this->instance($value, self::$map[$service]['arguments']);
			}

			return self::$services[$service];
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($service, $value)
	{
		self::$map[$service] = $value;
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($service)
	{
		if ($this->offsetExists($service)) {
			unset(self::$map[$service]);
		}
	}
}
