<?php
namespace Core\Lib\Data;

/**
 * Field Class
 *
 * Element of a data container. Wrapper for data provided by DataAdapter.
 * Each field has it's own definiton. Flags like serialize or primary can
 * be used to control how data has.
 *
 * Implements ArrayAccess interface to use object like an array.
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @version 1.0
 * @license MIT
 * @copyright 2014 by author
 */
class Field implements \ArrayAccess
{

	use \Core\Lib\Traits\SerializeTrait;

	/**
	 *
	 * @var string
	 */
	private $name;

	/**
	 *
	 * @var string
	 */
	private $type;

	/**
	 *
	 * @var number
	 */
	private $size;

	/**
	 *
	 * @var bool
	 */
	private $primary = false;

	/**
	 *
	 * @var mixed
	 */
	private $value = null;

	/**
	 *
	 * @var bool
	 */
	private $serialize = false;

	/**
	 *
	 * @var array
	 */
	private $validate = [];


	public function __construct() {
	}

	/**
	 * Returns fieldname.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Sets field name.
	 *
	 * @param string $name
	 *
	 * @return \Core\Lib\Data\Field
	 */
	public function setName($name)
	{
		$this->name = (string) $name;

		return $this;
	}

	/**
	 * Returns field type.
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Sets field type.
	 *
	 * @param $type
	 *
	 * @return \Core\Lib\Data\Field
	 */
	public function setType($type)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * Returns field size.
	 *
	 * @return number
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * Sets field size.
	 *
	 * Adds automatic validation check against the size.
	 *
	 * @param int $size
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @return \Core\Lib\Data\Field
	 */
	public function setSize($size)
	{
		if  (!is_numeric($size)) {
			Throw new \InvalidArgumentException('Only numbers are allowed as field size.');
		}

		$this->size = $size;

		$this->validate[] = [
			'max',
			$size
		];

		return $this;
	}

	/**
	 * Returns primary flag.
	 *
	 * @return bool
	 */
	public function getPrimary()
	{
		return $this->primary;
	}

	/**
	 * Sets primary flag.
	 *
	 * @param bool $primary
	 *
	 * @return \Core\Lib\Data\Field
	 */
	public function setPrimary($primary)
	{
		$this->primary = (bool) $primary;

		return $this;
	}

	/**
	 * Returns field value.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Returns field value. Same as getValue() only shorter.
	 *
	 * @return mixed
	 */
	public function get()
	{
		return $this->value();
	}

	/**
	 * Sets field value.
	 *
	 * Takes care of serialized data.
	 *
	 * @param mixed $value
	 *
	 * @return \Core\Lib\Data\Field
	 */
	public function setValue($value)
	{
		if ($this->serialize) {
			$value = unserialize($value);
		}

		// Is the data serialized?
		elseif ($this->isSerialized($value)) {
			$this->serialize = true;
			$value = unserialize($value);
		}

		$this->value = $value;

		return $this;
	}

	/**
	 * Get validation rules.
	 *
	 * @return array
	 */
	public function getValidattion()
	{
		return $this->validate;
	}

	/**
	 * Sets validation rule by resetting existing rules.
	 *
	 * @param string|array $rule Validation rule
	 *
	 * @return \Core\Lib\Data\Field
	 */
	public function setValidation($rule)
	{
		if (!is_array($rule)) {
			$rule = (array) $rule;
		}

		$this->validate = $rule;

		return $this;
	}

	/**
	 * Adds validation rule to already exsiting rules.
	 *
	 * @param string|array $rule Validation rule
	 *
	 * @return \Core\Lib\Data\Field
	 */
	public function addValidation($rule)
	{
		$this->validate[] = $rule;

		return $this;
	}

	/**
	 * Returns serialize flag
	 *
	 * @return bool
	 */
	public function getSerialize()
	{
		return $this->serialize;
	}

	/**
	 * Set serialize flag
	 *
	 * @param bool $serialize
	 *
	 * @return \Core\Lib\Data\Field
	 */
	public function setSerialize($serialize)
	{
		$this->serialize = (bool) $serialize;

		return $this;
	}

	/**
	 * Counts the field value and returns the result.
	 *
	 * Uses strlen() on strings
	 * Uses count() on arrays
	 * Uses field value when value is numeric
	 *
	 * @return number
	 */
	public function count() {
		if (is_string($this->value)) {
			return strlen($this->value);
		}

		if (is_numeric($this->value)) {
			return $this->value;
		}

		if (is_array($this->value)) {
			return count($this->value);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			Throw new \InvalidArgumentException('Anonymous data field access is not allowed. Provide a field name.');
		} else {
			$this->$offset = $value;
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset) {
		return isset($this->$offset);
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($offset) {
		unset($this->$offset);
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset) {
		if (isset($this->$offset)) {
			return $this->$offset;
		}
		else {
			Throw new \InvalidArgumentException('Field property "' . $offset . '" does not exists.');
		}
	}
}
