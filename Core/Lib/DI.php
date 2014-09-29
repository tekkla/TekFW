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

	private static $singletons = [];

	public function singleton($class_name, $arguments = null)
	{
		if (! isset(self::$singletons[$class_name])) {
			self::$singletons[$class_name] = $this->instance($class_name, $arguments);
		}

		return self::$singletons[$class_name];
	}

	public function factory($class_name, $arguments = null)
	{
		if ($arguments !== null && ! is_array($arguments)) {
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

		// And add this di container as last arguments
		$arguments[] = $this;

		$reflection = new \ReflectionClass($class_name);

		// Make sure the class has a factory method
		if (! $reflection->hasMethod('factory')) {
			Throw new \RuntimeException($class_name . '::factory() does not exist.');
		}

		return call_user_func_array($class_name . '::factory', $arguments);
	}

	public function instance($class_name, $arguments = null)
	{
		// initialized the ReflectionClass
		$reflection = new \ReflectionClass($class_name);

		// creating an instance of the class
		if ($arguments === null || count($arguments) == 0) {

			// Create instance without arguments
			$obj = new $class_name();
		}
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

	public function mapValue($key, $value)
	{
		self::$map[$key] = [
			'value' => $value,
			'type' => 'value'
		];
	}

	public function mapInstance($key, $value, $arguments = null)
	{
		self::$map[$key] = [
			'value' => $value,
			'type' => 'instance',
			'arguments' => $arguments
		];
	}

	public function mapSingleton($key, $value, $arguments = null)
	{
		self::$map[$key] = [
			'value' => $value,
			'type' => 'singleton',
			'arguments' => $arguments
		];
	}

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
	 * @throws MethodNotExistsError
	 * @throws ParameterNotSetError
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

	public function offsetExists($offset)
	{
		return array_key_exists($offset, self::$map);
	}

	public function offsetGet($offset)
	{
		if (! $this->offsetExists($offset)) {
			Throw new \InvalidArgumentException(sprintf('Service "%s" not mapped', $offset));
		}

		if (self::$map[$offset]['type'] == 'value') {
			return self::$map[$offset]['value'];
		}
		else {
			$method = self::$map[$offset]['type'];
			return $this->$method(self::$map[$offset]['value'], self::$map[$offset]['arguments']);
		}
	}

	public function offsetSet($offset, $value)
	{
		self::$map[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		if ($this->offsetExists($offset)) {
			unset(self::$map[$offset]);
		}
	}
}
