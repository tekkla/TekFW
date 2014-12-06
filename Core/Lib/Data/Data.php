<?php
namespace Core\Lib\Data;

/**
 * Class to provide an easy to use interface for reading/writing associative array based information
 * by exposing properties that represents each key of the array
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 */
class Data implements \IteratorAggregate
{

	/**
	 * Keeps the state of each property
	 *
	 * @var array
	 */
	private $properties = [];

	/**
	 * Keeps the current propertyname accessed by __get($name)
	 *
	 * @var string
	 */
	private static $cur;

	/**
	 * Creates a new Data instance initialized with $properties
	 */
	public function __construct($properties = [])
	{
		$this->populate($properties);
	}

	/**
	 * Return iterator
	 *
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->properties);
	}

	/**
	 * Creates properties for this instance whose names/contents are defined by the keys/values in the $properties associative array
	 */
	private function populate($properties)
	{
		foreach ($properties as $name => $value) {
			$this->create_property($name, $value);
		}
	}

	/**
	 * Creates a new property or overrides an existing one using $name as property name and $value as its value
	 */
	private function create_property($name, $value)
	{
		$this->properties[$name] = is_array($value) ? new self($value) : $value;
	}

	/**
	 * Gets the value of the property named $name
	 * If $name does not exists, it is initialized with an empty instance of MultiDimensionalObject before returning it
	 * By using this technique, we can initialize nested properties even if the path to them don't exist
	 * I.e.: $configfoo
	 * - property doesn't exists, it is initialized to an instance of MultiDimensionalObject and returned
	 *
	 * $configfoobar = "hello";
	 * - as explained before, doesn't exists, it is initialized to an instance of MultiDimensionalObject and returned.
	 * - when set to "hello"; bar becomes a string (it is no longer an MultiDimensionalObject instance)
	 */
	public function &__get($name)
	{
		// Set current accessname
		self::$cur = $name;

		$this->create_property_if_not_exists($name);
		return $this->properties[$name];
	}

	/**
	 * Creates a new property if it does not exists
	 *
	 * @param string $name
	 */
	private function create_property_if_not_exists($name)
	{
		if (array_key_exists($name, $this->properties)) {
			return;
		}

		$this->create_property($name, []);
	}

	/**
	 * Magic setter
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		$this->create_property($name, $value);
	}

	/**
	 * Magic unset
	 *
	 * @param string $name
	 */
	public function __unset($name)
	{
		unset($this->properties[$name]);
	}

	/**
	 * Magic isset
	 *
	 * @param string $name
	 */
	public function __isset($name)
	{
		return isset($this->properties[$name]);
	}

	/**
	 * Shows a missing info when accessing a none existing property
	 *
	 * @return string
	 */
	public function __toString()
	{
		return false;
	}

	/**
	 * Returns amount of current properties
	 *
	 * @return number
	 */
	public function count()
	{
		return count($this->properties);
	}

	/**
	 * Returns keys of current properties
	 *
	 * @return multitype:
	 */
	public function keys()
	{
		return array_keys($this->properties);
	}

	/**
	 * Returns all current properties
	 */
	public function getProperties()
	{
		return $this->properties;
	}

	/**
	 * Checks for empty propoerties
	 */
	public function isEmpty()
	{
		return empty($this->properties);
	}
}
