<?php
namespace Core\Lib\Data;

use Core\Lib\Data\Validator\Validator;

/**
 *
 * @author Michael
 *
 */
class Container implements \IteratorAggregate, \ArrayAccess
{

	private $fields = [];

	private $errors = [];

	/**
	 * Constructor
	 *
	 * Field need at least a type definition.
	 *
	 * @param array $fields
	 */
	public function __construct(array $fields = [])
	{
		foreach ($fields as $name => $field) {

			if (! isset($field['type'])) {
				$field['type'] = 'string';
			}

			$field['primary'] = isset($field['primary']) ? true : false;
			$field['serialize'] = isset($field['serialize']) ? true : false;
			$field['validate'] = isset($field['validate']) ? $field['validate'] : [];

			// Attach always an empty validation rule to primary fields
			if ($field['primary'] && ! in_array('empty', $field['validate'])) {
				$field['validate'][] = 'empty';
			}

			if (! isset($field['size'])) {
				$field['size'] = null;
			}

			$this->createField($name, $field['type'], $field['primary'], $field['size'], $field['serialize'], $field['validate']);
		}
	}

	/**
	 * Access on field value.
	 *
	 * @param string $name Name of field
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->fields[$name]->getValue();
	}

	/**
	 * Return iterator
	 *
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->fields);
	}

	/**
	 * Creates a container field and adds it to the container
	 *
	 * @param string $name Fieldname
	 * @param string $type Fieldtype
	 * @param boolean $primary Primary key flag
	 * @param boolean $serialize Serialize flag
	 * @param string|array $validate One or more validation rules
	 *
	 * @return \Core\Lib\Data\Container
	 */
	public function createField($name, $type, $size = null, $primary = false, $serialize = false, $validate = [])
	{
		$data_field = new Field();
		$data_field->setName($name);
		$data_field->setType($type);

		if ($size !== null) {
			$data_field->setSize($size);
		}

		$data_field->setPrimary($primary);
		$data_field->setSerialize($serialize);
		$data_field->setValidate($validate);

		$this->fields[$name] = $data_field;

		$this;
	}

	public function addField(Field $field)
	{
		$this->fields[$field->getName()] = $field;

		return $this;
	}

	/**
	 * Sets one or more validation rule for a specific field.
	 *
	 * @param string $field Fieldname
	 * @param string|array $rule Rulename or an array of rulenames
	 *
	 * @return \Core\Lib\Data\DataContainer
	 */
	public function setValidation($field, $rule)
	{
		$this->field[$field]->setValidate(is_array($rule) ? $rule : (array) $rule);

		return $this;
	}

	/**
	 * Validates container data against the set validation rules
	 */
	public function validate()
	{
		$validator = new Validator($this);
		$validator->validate();
	}

	/**
	 * Sets an field related error.
	 *
	 * @param string $field Fieldname
	 * @param string $error Errortext
	 *
	 * @return \Core\Lib\Data\DataContainer
	 */
	public function setError($field, $error)
	{
		$this->erros[$field][] = $error;

		return $this;
	}

	/**
	 * Get all field specific errors as an array.
	 *
	 * @param string $field
	 *
	 * @return array
	 */
	public function getFieldErrors($field)
	{
		return isset($this->errors[$field]) ? $this->errors[$field] : [];
	}

	/**
	 * Returns all errors
	 *
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Fills container with data.
	 *
	 * @param array $data
	 *
	 * @throws \RuntimeException
	 *
	 * @return \Core\Lib\Data\DataContainer
	 */
	public function fill(array $data)
	{
		foreach ($data as $name => $value) {

			if (in_array($name, array_keys($this->fields))) {

				if ($this->fields[$name]->getSerialize()) {
					$value = unserialize($value);
				}

				if (! $value instanceof Container && ! is_object($value) && ! is_array($value) && ! is_null($value)) {
					settype($value, $this->fields[$name]->getType());
				}

				if (is_array($value)) {
					$this->fields[$name]->setType('array');
				}

				$this->fields[$name]->setValue($value);
			}
			else {
				Throw new \RuntimeException('Cannot fill data into container. Field "' . $name . '" does not exist in container.');
			}
		}

		return $this;
	}

	/**
	 * Returns all fields as array
	 *
	 * @return array
	 */
	public function get()
	{
		$this->fields();
	}

	public function offsetSet($offset, $value)
	{
		if (is_null($offset)) {
			Throw new \InvalidArgumentException('You can not add an anonymous field to a container. Please provide a unique name.');
		}

		if (! $value instanceof Field) {

			var_dump($value);

			Throw new \InvalidArgumentException('Only Field objects can be added to a container.');
		}

		$this->fields[$offset] = $value;
	}

	public function offsetExists($offset)
	{
		return isset($this->fields[$offset]);
	}

	public function offsetUnset($offset)
	{
		unset($this->fields[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->fields[$offset]) ? $this->fields[$offset]->getValue() : null;
	}
}
