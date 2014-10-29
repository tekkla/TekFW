<?php
namespace Core\Lib\Data;

use Core\Lib\Data\Validator\Validator;

/**
 *
 * @author Michael
 *
 */
class Container implements \IteratorAggregate
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
		foreach ($fields as $field) {

			if (! isset($field['name'])) {
				Throw new \RuntimeException('DataContainer fields do need a name. Check your field definition.');
			}

			if (! isset($field['type'])) {
				Throw new \RuntimeException('DataContainer fields do need a datatype. Check your field definition.');
			}

			$this->createField($field['name'], $field['type'], isset($field['primary']) ? (bool) $field['primary'] : false, isset($field['serialize']) ? (bool) $field['serialize'] : false, isset($field['validate']) ? $field['validate'] : []);
		}
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
	public function createField($name, $type, $primary = false, $serialize = false, $validate = [])
	{
		$data_field = new Field();
		$data_field->setName($name);
		$data_field->setType($type);
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

				if (! is_object($value) && !is_array($value) && !is_null($value)) {
					settype($value, $this->fields[$name]->getType());
				}

				$this->fields[$name]->setValue($value);

			} else {
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
}
