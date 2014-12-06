<?php
namespace Core\Lib\Data;

/**
 *
 * @author Michael
 *
 */
class Container implements \IteratorAggregate, \ArrayAccess
{

    use\Core\Lib\Traits\SerializeTrait;

    private $fields = [];

    private $errors = [];

    /**
     * Constructor
     *
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        if ($fields) {
            $this->parseFields($fields);
        }
    }

    /**
     * Access on field.
     *
     * @param string $name
     *            Name of field
     *
     * @return mixed
     */
    public function &__get($name)
    {
        $null = null;

        if (isset($this->fields[$name])) {
            return $this->fields[$name];
        } else {
            return $null;
        }
    }

    /**
     * Unsets field
     *
     * @param string $name
     */
    public function __unset($name)
    {
        if (isset($this->fields[$name])) {
            unset($this->fields[$name]);
        }
    }

    /**
     * Isset on fields
     *
     * @param string $name
     */
    public function __isset($name)
    {
        return isset($this->fields[$name]);
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
     * Parses a field definition array and created container fields from it. A field withou type attribute is treated as
     * string. You can use the following attributes:
     *
     * type         => String, integer, float or any other datatype you want to use. Be sure a componenten like
     *                 DataAdapter can handle this datatype. Default: string
     * primary      => Flag to show that this field contains the value of a primary key. Default: false
     * serialize    => Flag that says the Data needs to be serialized before saving and to be unserialized before
     *                 fillig the field. Default: false
     * validate     => Array of rules to validate the field value angainst. Default: []
     * size         => Max contentsize of field. Defaul: null
     *
     * @param array $fields
     */
    public function parseFields(Array $fields)
    {
        foreach ($fields as $name => $field) {

            if (! isset($field['type'])) {
                $field['type'] = 'string';
            }

            $field['primary'] = isset($field['primary']) ? (bool) $field['primary'] : false;
            $field['serialize'] = isset($field['serialize']) ? (bool) $field['serialize'] : false;
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
     * Creates a container field and adds it to the container
     *
     * @param string $name
     *            Fieldname
     * @param string $type
     *            Fieldtype
     * @param boolean $primary
     *            Primary key flag
     * @param boolean $serialize
     *            Serialize flag
     * @param string|array $validate
     *            One or more validation rules
     *
     * @return \Core\Lib\Data\Container
     */
    public function createField($name, $type='string', $size = null, $primary = false, $serialize = false, $validate = [])
    {
        $data_field = new Field();
        $data_field->setName($name);
        $data_field->setType($type);

        if ($size !== null) {
            $data_field->setSize($size);
        }

        $data_field->setPrimary($primary);
        $data_field->setSerialize($serialize);
        $data_field->setValidation($validate);

        $this->fields[$name] = $data_field;

        return $this;
    }

    public function addField(Field $field)
    {
        $this->fields[$field->getName()] = $field;

        return $this;
    }

    /**
     * Sets one or more validation rule for a specific field.
     *
     * @param string $field
     *            Fieldname
     * @param string|array $rule
     *            Rulename or an array of rulenames
     *
     * @return \Core\Lib\Data\DataContainer
     */
    public function setValidation($field, $rule)
    {
        if (! isset($this->fields[$field])) {
            $this->createField($field, 'string');
        }

        $this->fields[$field]->setValidation(is_array($rule) ? $rule : (array) $rule);

        return $this;
    }

    /**
     * Validates container data against the set validation rules
     */
    public function validate()
    {
        $validator = $this->di->get('core.data.validator');
        $validator->setContainer($this);
        $validator->validate();
    }

    /**
     * Adds a field related error message.
     *
     * @param string $field
     *            Fieldname
     * @param string $error
     *            Errortext
     *
     * @return \Core\Lib\Data\DataContainer
     */
    public function addError($field, $error)
    {
        $this->errors[$field][] = $error;

        return $this;
    }

    /**
     * Sets a field related error message.
     *
     * @param string $field
     *            Fieldname
     * @param string $error
     *            Errortext
     *
     * @deprecated
     *
     * @return \Core\Lib\Data\DataContainer
     */
    public function setError($field, $error)
    {
        return $this->addError($field, $error);
    }

    /**
     * Get all field specific errors as an array.
     *
     * @param string $field
     *
     * @return array
     */
    public function getErrors($field = '')
    {
        if (! $field) {
            return $this->errors;
        }

        return isset($this->errors[$field]) ? $this->errors[$field] : [];
    }

    public function hasErrors()
    {
        return $this->errors ? true : false;
    }

    /**
     * Fills container with data.
     *
     * Tries to use an existing field and creates a generic string field when no matching field is found.
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

            // Not existing field? Create a generic one.
            if (! in_array($name, array_keys($this->fields))) {
                $this->createField($name, 'string');
            }

            if (! $value instanceof Container && ! is_object($value) && ! is_array($value) && ! is_null($value)) {
                settype($value, $this->fields[$name]->getType());
            }

            // Simple array check to determine array field type and set serialize flag
            if (is_array($value)) {
                $this->fields[$name]->setType('array');
                $this->fields[$name]->setSerialize(true);
            }

            $this->fields[$name]->setValue($value);
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
        return $this->fields;
    }

    /**
     * Returns array with names of fields in container.
     *
     * @return array
     */
    public function getFieldNames()
    {
        return array_keys($this->fields);
    }

    /**
     * Returns areference to a conatiner field.
     *
     * @param string $field
     *
     * @return Field
     */
    public function &getField($field)
    {
        return $this->fields[$field];
    }

    /**
     * Sets serialize flag on fields provided by fields argument.
     *
     * @param string|array $fields
     *            One field or list of fields to set flag
     *
     * @return \Core\Lib\Data\Container
     */
    public function setSerialize($fields = [])
    {
        if (! is_array($fields)) {
            $fields = (array) $fields;
        }

        foreach ($fields as $field) {
            $this->fields[$field]->setSerialize(true);
        }

        return $this;
    }

    public function getColumn($field)
    {}

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            Throw new \InvalidArgumentException('You can not add an anonymous field to a container. Please provide a unique name.');
        }

        if (! isset($this->fields[$offset])) {
            switch (true) {

                case (is_array($value)):
                    $type = 'array';
                    break;
                case ($value instanceof Container):
                    $type = 'array';
                    $value = $value->get();
                    break;
                default:
                    $type = 'string';
                    break;
            }

            $this->createField($offset, $type);
        }

        $this->fields[$offset]->setValue($value);
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        if (!isset($this->fields[$offset])) {
            return false;
        }

        // Field found, get value to check for null
        $value = $this->fields[$offset]->getValue();

        return is_null($value) ? false : true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        unset($this->fields[$offset]);
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetGet()
     *
     * @return Field
     */
    public function offsetGet($offset)
    {
        return isset($this->fields[$offset]) ? $this->fields[$offset]->getValue() : false;
    }
}
