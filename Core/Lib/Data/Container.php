<?php
namespace Core\Lib\Data;

use Core\Lib\Traits\SerializeTrait;
use Core\Lib\Traits\DebugTrait;

/**
 * Container Object
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015 by author
 * @license MIT
 */
class Container implements \IteratorAggregate, \ArrayAccess
{

    use SerializeTrait;
    use DebugTrait;

    /**
     * Optional name of fieldlist to load
     *
     * @var string
     */
    protected $available = '';

    /**
     * List of fieldnames to use from available fields
     *
     * @var array
     */
    protected $use = [];

    /**
     * Storage for field objects
     *
     * @var array
     */
    private $fields = [];

    /**
     * Storage of container error messages
     *
     * @var array
     */
    private $errors = [];

    /**
     * Constructor
     *
     * @param array $fields
     */
    public function __construct()
    {}

    /**
     * Access on field.
     *
     * @param string $name Name of field
     *
     * @return mixed
     */
    public function &__get($name)
    {
        $null = null;

        if (isset($this->fields[$name])) {
            return $this->fields[$name];
        }
        else {
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
     * Parses a field definition array and created container fields from it.
     * A field withou type attribute is treated as string.
     *
     * You can use the following attributes:
     *
     * type => String, integer, float or any other datatype you want to use. Be sure a componenten like
     * DataAdapter can handle this datatype. Default: string
     * primary => Flag to show that this field contains the value of a primary key. Default: false
     * serialize => Flag that says the Data needs to be serialized before saving and to be unserialized before
     * fillig the field. Default: false
     * validate => Array of rules to validate the field value angainst when calling containers validate() method. Default: []
     * size => Max contentsize of field. Defaul: null
     * control => Name of controltype to use when container is used in forms or within FormDesinger lib.
     * default => Defaultvalue for this field.
     *
     * @param array $fields
     */
    public function parseFields(Array $fields = [])
    {
        if (empty($fields) && ! empty($this->use)) {
            foreach ($this->use as $fld_name) {
                $fields[$fld_name] = $this->available[$fld_name];
            }
        }

        // When there is no field definition, than try to load this defintions
        if (empty($fields) && ! empty($this->available) && is_array($this->available)) {

            // The field defintion list can be stored in use property
            $fields = $this->available;
        }

        // Field creation process
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

            // Nullify fields when not set
            $null_fields = [
                'size',
                'control',
                'default'
            ];

            foreach ($null_fields as $to_check) {
                if (! isset($field[$to_check])) {
                    $field[$to_check] = null;
                }
            }

            $this->createField($name, $field['type'], $field['size'], $field['primary'], $field['serialize'], $field['validate'], $field['control'], $field['default']);
        }
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
    public function createField($name, $type = 'variant', $size = null, $primary = false, $serialize = false, $validate = [], $control = null, $default = null)
    {
        $field = new Field();

        $field->setName($name);
        $field->setType($type);

        if ($size !== null) {
            $field->setSize($size);
        }

        if ($control !== null) {
            $field->setControl($control);
        }

        if ($default !== null) {
            $field->setDefault($default);
        }

        $field->setPrimary($primary);
        $field->setSerialize($serialize);
        $field->setValidation($validate);

        $this->fields[$name] = $field;

        return $this;
    }

    /**
     * Adds a Field object to the fieldlist.
     *
     * @param Field $field
     *
     * @return \Core\Lib\Data\Container
     */
    public function addField(Field $field)
    {
        $this->fields[$field->getName()] = $field;

        return $this;
    }

    /**
     * Removes a field by it's name from the fieldlist.
     *
     * @param string $name
     *
     * @return \Core\Lib\Data\Container
     */
    public function removeField($name)
    {
        if (isset($this->fields[$name])) {
            unset($this->fields[$name]);
        }

        return $this;
    }

    /**
     * Adds validation rules by providing a complete stack of rules for more than one field.
     *
     * @param array $validationset
     *
     * @return \Core\Lib\Data\Container
     */
    public function setValidationset(Array $validationset)
    {
        foreach ($validationset as $field => $rule) {
            $this->setValidation($field, $rule);
        }

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
        if (! isset($this->fields[$field])) {
            $this->createField($field, 'string');
        }

        $this->fields[$field]->setValidation(is_array($rule) ? $rule : (array) $rule);

        return $this;
    }

    /**
     * Validates container data against the set validation rules.
     *
     * @return boolean
     */
    public function validate()
    {
        /* @var $validator \Core\Lib\Data\Validator\Validator */
        $validator = $this->di->get('core.data.validator');

        /* @var $field \Core\Lib\Data\Field */
        foreach ($this->fields as $field) {

            /* @TODO CHECK THIS TO APPLY RULES FOR PK NEEDED? */
            if ($field->getPrimary()) {
                continue;
            }

            // Get rules from field
            $rules = $field->getValidation();

            // No rules no valiadtion for this field
            if (empty($rules)) {
                continue;
            }

            $result = $validator->validate($field->getValue(), $rules);

            if (empty($result)) {
                continue;
            }

            $this->addError($field->getName(), $result);
        }

        return $this->hasErrors();
    }

    /**
     * Adds a field related error message.
     *
     * @param string $field Fieldname
     * @param string|array $error Errortext
     *
     * @return \Core\Lib\Data\DataContainer
     */
    public function addError($field, $error)
    {
        if (is_array($error)) {
            foreach ($error as $msg) {
                $this->errors[$field][] = $msg;
            }
        }
        else {
            $this->errors[$field][] = $error;
        }

        return $this;
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
        if (empty($field)) {
            return $this->errors;
        }

        return isset($this->errors[$field]) ? $this->errors[$field] : false;
    }

    /**
     * Checks for errors in the container
     *
     * @return boolean
     */
    public function hasErrors($field = '')
    {
        // Check field specific error
        if (! empty($field) && ! empty($this->errors)) {
            return isset($this->errors[$field]);
        }

        return $this->errors ? true : false;
    }

    /**
     * Fills container with data.
     *
     * Tries to use an existing field and creates a generic string field when no matching field is found.
     * Important: The filled in data will be converted explicite into the type the field is set to.
     *
     * @param array $data
     * @param array $validationset
     * @param array $serialize Array of fieldnames to
     *
     * @throws \RuntimeException
     *
     * @return \Core\Lib\Data\DataContainer
     */
    public function fill(Array $data, Array $validationset = [], Array $serialize = [])
    {
        foreach ($data as $name => $value) {

            // Not existing field? Create a generic one.
            if (! in_array($name, array_keys($this->fields))) {
                $this->createField($name, 'string');
            }

            // Important: Explicite txype conversion!
            if (! $value instanceof Container && ! is_object($value) && ! is_array($value) && ! is_null($value)) {
                settype($value, $this->fields[$name]->getType());
            }

            // Simple array check to determine array field type and set serialize flag
            if (is_array($value)) {
                $this->fields[$name]->setType('array');
                $this->fields[$name]->setSerialize(true);
            }

            if ($value instanceof Container) {
                $this->fields[$name]->setType('container');
                $this->fields[$name]->setSerialize(true);
            }

            $this->fields[$name]->setValue($value);
        }

        if ($validationset) {
            $this->setValidationset($validationset);
        }

        if ($serialize) {
            $this->setSerialize($serialize);
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
     * Returns the fields by their names their values as assoc array.
     *
     * @return array
     */
    public function getArray()
    {
        $out = [];

        foreach ($this->fields as $name => $field) {
            $out[$name] = $field->getValue();
        }

        return $out;
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
     * @param string|array $fields One field or list of fields to set flag
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

    public function useAllFields()
    {
        $this->use = array_keys($this->available);

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
                case (is_string($value)):
                    $type = 'string';
                    break;
                default:
                    $type = 'variant';
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
        if (! isset($this->fields[$offset])) {
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
