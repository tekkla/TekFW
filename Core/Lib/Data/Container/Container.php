<?php
namespace Core\Lib\Data\Container;

// Validator Libs
use Core\Lib\Data\Validator\Validator;

/**
 * Container.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Container implements \IteratorAggregate, \ArrayAccess
{

    /**
     * Field definition array
     *
     * @var array
     */
    protected $available = [];

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
    protected $fields = [];

    /**
     * Storage of container error messages
     *
     * @var array
     */
    private $errors = [];

    /**
     * Access on field
     *
     * @param string $name
     *            Name of field
     *
     * @return null:mixed
     */
    public function &__get($name)
    {
        $null = null;

        $this->checkFieldExists($name);

        return $this->fields[$name];
    }

    /**
     * Unsets a field
     *
     * @param string $name
     *            Name of the field to unset
     *
     * @return \Core\Lib\Data\Container\Container
     *
     */
    public function __unset($name)
    {
        $this->checkFieldExists($name);

        unset($this->fields[$name]);

        return $this;
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

    public function __sleep()
    {
        return [
            'available',
            'use',
            'fields'
        ];
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
     * validate => Array of rules to validate the field value angainst when calling containers validate() method.
     * Default: []
     * size => Max contentsize of field. Defaul: null
     * control => Name of controltype to use when container is used in forms or within FormDesinger lib.
     * default => Defaultvalue for this field.
     *
     * @param array $use
     *            Optional array of fieldnames to use
     *
     * @throws UnexpectedValueException
     */
    public function parseFields(Array $use = [])
    {
        if (! empty($use)) {
            $this->use = $use;
        }

        // Field creation process
        foreach ($this->use as $name) {

            if (! array_key_exists($name, $this->available)) {
                Throw new ContainerException(sprintf('The field "%s" does not exist in container "%s"', $name, get_called_class()));
            }

            $field = $this->available[$name];

            if (! array_key_exists('type', $field)) {
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
                'size' => null,
                'control' => null,
                'default' => null,
                'filter' => []
            ];

            foreach ($null_fields as $to_check => $empty_value) {
                if (! isset($field[$to_check])) {
                    $field[$to_check] = $empty_value;
                }
            }

            $this->createField($name, $field['type'], $field['size'], $field['primary'], $field['serialize'], $field['validate'], $field['control'], $field['default'], $field['filter']);
        }
    }

    /**
     * Creates a container field, adds it to the container and returns e reference to this field
     *
     * @param string $name
     *            Name of field
     * @param string $type
     *            Optional data type of field. (Default: variant)
     * @param string $size
     *            Optional size of the field. (Default: null)
     * @param string $primary
     *            Optional flag to set a field as primary key (Default: false)
     * @param string $serialize
     *            Optional flag for value serialization (Default: false)
     * @param array $validate
     *            Optional set of validation rules (Default: [])
     * @param string $control
     *            Optional control type (Default: null)
     * @param string $default
     *            Optional default value (Default: null)
     * @param string|array $filter
     *            Optional filter statements (Default: [])
     *
     * @return \Core\Lib\Data\Container\Field
     */
    public function &createField($name, $type = 'variant', $size = null, $primary = false, $serialize = false, $validate = [], $control = null, $default = null, $filter = [])
    {
        $field = new Field();

        if ($default !== null) {
            $field->setDefault($default);
        }

        $field->setName($name);
        $field->setType($type);

        if ($size !== null) {
            $field->setSize($size);
        }

        if ($control !== null) {
            $field->setControl($control);
        }

        if (! empty($filter)) {
            $field->setFilter($filter);
        }

        $field->setPrimary($primary);
        $field->setSerialize($serialize);
        $field->setValidation($validate);

        $this->fields[$name] = $field;

        return $field;
    }

    /**
     * Adds a Field object to the fieldlist
     *
     * @param Field $field
     *            Field object to add
     *
     * @return \Core\Lib\Data\Container\Container
     */
    public function addField(Field $field)
    {
        $this->fields[$field->getName()] = $field;

        return $this;
    }

    /**
     * Removes a field by it's name from the fieldlist
     *
     * @param string $name
     *            Name of field to remove
     *
     * @return \Core\Lib\Data\Container\Container
     */
    public function removeField($name)
    {
        $this->checkFieldExists($name);

        unset($this->fields[$name]);

        return $this;
    }

    /**
     * Adds validation rules by providing a complete stack of rules for more than one field
     *
     * @param array $validationset
     *            Array of validator rules
     *
     * @return \Core\Lib\Data\Container\Container
     */
    public function setValidationset(Array $validationset)
    {
        foreach ($validationset as $field => $rule) {
            $this->setValidation($field, $rule);
        }

        return $this;
    }

    /**
     * Sets one or more validation rule for a specific field
     *
     * @param string $name
     *            Name of the field
     * @param string|array $rule
     *            Rulename or an array of rulenames
     *
     * @return \Core\Lib\Data\DataContainer
     */
    public function setValidation($name, $rule)
    {
        $this->checkFieldExists($name);

        $this->fields[$name]->setValidation(is_array($rule) ? $rule : (array) $rule);

        return $this;
    }

    /**
     * Returns the validation rules of a field
     *
     * @param string $name
     *            Name of the field
     *
     * @throws DataException
     *
     * @return array
     */
    public function getValidation($name)
    {
        $this->checkFieldExists($name);

        return $this->fields[$name]->getValidation();
    }

    /**
     * Validates container data against the set validation rules
     *
     * Returns boolean true when successful validate without errors.
     *
     * @param array $skip
     *            Optional array of fieldnames to skip on validation
     *
     * @return boolean
     */
    public function validate(array $skip = [])
    {
        $validator = new Validator();

        /* @var $field \Core\Lib\Data\Container\Field */
        foreach ($this->fields as $name => $field) {

            // Skip field?
            if (in_array($name, $skip)) {
                continue;
            }

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

            $this->addError($name, $result);
        }

        return $this->hasErrors() ? false : true;
    }

    /**
     * Runs filter() method on each field in container
     *
     * @return Container
     */
    public function filter()
    {
        /* @var $field \Core\Lib\Data\Container\Field */
        foreach ($this->fields as $field) {
            $field->filter();
        }

        return $this;
    }

    /**
     * Adds a field related error message
     *
     * @param string $field
     *            Fieldname
     * @param string|array $error
     *            Errortext
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
     * Returns boolean false when there are no errors.
     *
     * @param string $name
     *            Optional name of a field to get errors of.
     *            When empty all errors of all fields will be returned. (Default: '')
     *
     * @return array:boolean
     */
    public function getErrors($name = '')
    {
        if (empty($name)) {
            return empty($this->errors) ? false : $this->errors;
        }

        $this->checkFieldExists($name);

        return isset($this->errors[$name]) ? $this->errors[$name] : false;
    }

    /**
     * Checks for errors in the container
     *
     * @param string $field
     *            Optional name of a field to get errors of.
     *            When empty all errors of all fields will be returned. (Default: '')
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
     *            The data to fill in container
     * @param array $validationset
     *            Optional set of validation rules
     * @param array $serialize
     *            Array of fieldnames to flag as serializable
     * @param boolean $autofilter
     *            Optional flag to autofilter data before storing as value. (Default: false)
     *
     * @todo Have a look on autofilter and the way validation rules are stored. Is it neccessary to add nore filterrules
     *       like it's done with validationset or should this be removed completely? Maybe it is better do add both,
     *       validation- and filterrules, after a Container object has been created?
     *
     * @return \Core\Lib\Data\DataContainer
     */
    public function fill(Array $data, Array $validationset = [], Array $serialize = [], $autofilter = false)
    {
        foreach ($data as $name => $value) {

            // Not existing field? Create a generic one.

            /* @var $field \Core\Lib\Data\Container\Field */
            $field = ! in_array($name, array_keys($this->fields)) ? $this->createField($name, 'string') : $this->fields[$name];

            // Important: explicite type conversion!
            if (! $value instanceof Container && ! is_object($value) && ! is_array($value) && ! is_null($value)) {
                settype($value, $field->getType());
            }

            // Simple array check to determine array field type and set serialize flag
            if (is_array($value)) {
                $field->setType('array');
                $field->setSerialize(true);
            }

            if ($value instanceof Container) {
                $field->setType('container');
                $field->setSerialize(true);
            }

            $field->setValue($value);

            // Filter only on demand
            if ($autofilter) {
                $field->filter();
            }
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
     * Returns the fields by their names their values as assoc array
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
     * Returns array with names of fields in container
     *
     * @return array
     */
    public function getFieldNames()
    {
        return array_keys($this->fields);
    }

    /**
     * Returns areference to a conatiner field
     *
     * @param string $field
     *            Name of the field
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
     * @return \Core\Lib\Data\Container\Container
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

    /**
     * Checks fields in container for a primary field and returns the name of the field when found
     *
     * Returns boolean false when no such field exists.
     *
     * @return string|bool
     */
    public function getPrimary()
    {
        $primary = false;

        foreach ($this->fields as $field) {
            if ($field->getPrimary()) {
                $primary = $field->getName();
                break;
            }
        }

        return $primary;
    }

    /**
     * Sets a field as primary key field
     *
     * Only one field can be a primary field. When useing this method another field flagged as primary gets replaced by
     * this field.
     *
     * @param string $name
     *            Name of the field to flag as primary
     *
     * @return \Core\Lib\Data\Container\Container
     */
    public function setPrimary($name)
    {
        $this->checkFieldExists($name);

        /* @var $field \Core\Lib\Data\Container\Field */
        foreach ($this->fields as $field) {

            // Field flagged as primary?
            if ($field->getPrimary()) {

                // Unflag field and stop iteration
                $field->setPrimary(false);
                break;
            }
        }

        // set our new primary field
        $this->field[$name]->setPrimary(true);

        return $this;
    }

    /**
     * Returns camelized containers app name
     *
     * @return string
     */
    public function getAppName()
    {
        return explode('\'', __NAMESPACE__)[2];
    }

    /**
     * Returns camelized name of the container
     *
     * @return string
     */
    public function getContainerName()
    {
        return str_replace('Container', '', get_class($this));
    }

    private function checkFieldExists($name)
    {
        // Check for field exists
        if (! array_key_exists($name, $this->fields)) {
            Throw new ContainerException(sprintf('A field with the name "%s" does not exist in container "%s"', $name, get_called_class()));
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetSet()
     *
     * @throws InvalidArgumentException
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            Throw new ContainerException('You can not add an anonymous field to a container. Please provide a unique name.');
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
