<?php
namespace Core\Lib\Data;

/**
 *
 * @author Michael
 *
 */
class Field
{

	private $name;

	private $type;

	private $primary = false;

	private $value = null;

	private $serialize = false;

	private $validate = [];

	public function __construct() {

	}

	/**
	 *
	 * @return the unknown_type
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 *
	 * @param unknown_type $name
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 *
	 * @return the unknown_type
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 *
	 * @param unknown_type $type
	 */
	public function setType($type)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 *
	 * @return the unknown_type
	 */
	public function getPrimary()
	{
		return $this->primary;
	}

	/**
	 *
	 * @param unknown_type $primary
	 */
	public function setPrimary($primary)
	{
		$this->primary = $primary;

		return $this;
	}

	/**
	 *
	 * @return the unknown_type
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 *
	 * @param unknown_type $value
	 */
	public function setValue($value)
	{
		$this->value = $value;

		return $this;
	}

	/**
	 *
	 * @return the unknown_type
	 */
	public function getValidate()
	{
		return $this->validate;
	}

	/**
	 *
	 * @param unknown_type $validate
	 */
	public function setValidate($validate)
	{
		$this->validate = $validate;

		return $this;
	}

	/**
	 *
	 * @return the unknown_type
	 */
	public function getSerialize()
	{
		return $this->serialize;
	}

	/**
	 *
	 * @param unknown_type $serialize
	 */
	public function setSerialize($serialize)
	{
		$this->serialize = $serialize;

		return $this;
	}


}
