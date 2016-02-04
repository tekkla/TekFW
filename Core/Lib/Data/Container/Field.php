<?php
namespace Core\Lib\Data\Container;

// Traits
use Core\Lib\Traits\StringTrait;

/**
 * Field.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Field implements \ArrayAccess
{

    use StringTrait;

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
    private $value = '';

    /**
     *
     * @var string
     */
    private $control = 'text';

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

    /**
     *
     * @var mixed
     */
    private $default = '';

    /**
     *
     * @var array
     */
    private $filter = [];

    /**
     *
     * @var array
     */
    private $custom_properties = [];

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }

    /**
     * Returns fieldname
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets field name
     *
     * @param string $name
     *            The name of the field
     *
     * @return \Core\Lib\Data\Container\Field
     */
    public function setName($name)
    {
        $this->name = (string) $name;

        return $this;
    }

    /**
     * Returns field type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets field type
     *
     * @param string $type
     *            Datatype of field
     *
     * @return \Core\Lib\Data\Container\Field
     */
    public function setType($type)
    {
        $this->type = $type;

        // Call explicite type conversion
        $this->convValueToType();

        return $this;
    }

    /**
     * Converts the field value explicite to the var type specified
     */
    private function convValueToType()
    {
        switch ($this->type) {
            case 'int':
            case 'integer':
                $this->value = (int) $this->value;
                break;
            case 'bool':
            case 'boolean':
                $this->value = (bool) $this->value;
                break;
            case 'float':
            case 'double':
            case 'real':
                $this->value = (float) $this->value;
                break;
            case 'array':
                $this->value = (array) $this->value;
                break;
            case 'object':
                $this->value = (object) $this->value;
                break;
            case 'string':
            default:
                $this->value = (string) $this->value;
                break;
        }
    }

    /**
     * Returns field size
     *
     * @return number
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Sets field size
     *
     * Adds automatic validation check against the size and optionally set min value.
     *
     * @param int $size
     *            Size of the field
     * @param int $min
     *            Optional minimum value the field needs
     *
     * @throws FieldException
     *
     * @return \Core\Lib\Data\Container\Field
     */
    public function setSize($size, $min = false)
    {
        $size = (int) $size;

        if (empty($size)) {
            Throw new FieldException('field size cannot be zero.');
        }

        $this->size = $size;

        if ($min) {
            $rule = [
                'range',
                [
                    $min,
                    $size
                ]
            ];
        }
        else {
            $rule = [
                'max',
                $size
            ];
        }
        $this->validate[] = $rule;

        return $this;
    }

    /**
     * Returns primary flag
     *
     * @return bool
     */
    public function getPrimary()
    {
        return $this->primary;
    }

    /**
     * Sets primary flag
     *
     * @param bool $primary
     *            Boolean flag to set this field as primary key
     *
     * @return \Core\Lib\Data\Container\Field
     */
    public function setPrimary($primary)
    {
        $this->primary = (bool) $primary;

        return $this;
    }

    /**
     * Returns field value
     *
     * Filters value with set field filters before returning it.
     *
     * @return mixed
     */
    public function getValue()
    {
        $this->filter();

        return $this->value;
    }

    /**
     * Sets field value
     *
     * Takes care of serialized data.
     *
     * @param mixed $value
     *            Value to set
     * @param string $type
     *            Optional type of value
     *
     * @return \Core\Lib\Data\Container\Field
     */
    public function setValue($value, $type = null)
    {
        // Is the data serialized?
        if ($this->stringIsSerialized($value)) {
            $this->serialize = true;
            $value = unserialize($value);
        }

        $this->value = $value;

        if (! empty($type)) {

            // Set field type
            $this->setType($type);
        }
        else {

            // Call explicite type conversion
            $this->convValueToType();
        }

        return $this;
    }

    /**
     * Sets the control to use when field used in displayfunctions
     *
     * @param string $control_type
     *
     * @return \Core\Lib\Data\Container\Field
     */
    public function setControl($control_type)
    {
        $this->control = $control_type;

        return $this;
    }

    /**
     * Returns set control type
     *
     * @return string
     */
    public function getControl()
    {
        return $this->control;
    }

    /**
     * Sets fields default value
     *
     * @param mixed $default_value
     *
     * @return \Core\Lib\Data\Container\Field
     */
    public function setDefault($default_value)
    {
        $this->default = $default_value;

        // @TODO Remember this when setting default values over 0
        if (empty($this->value)) {
            $this->value = $default_value;
        }

        return $this;
    }

    /**
     * Returns set default value
     *
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Returns set validation rules
     *
     * @return array
     */
    public function getValidation()
    {
        return $this->validate;
    }

    /**
     * Sets validation rule while overwriting existing rules
     *
     * @param string|array $rule
     *            Validation rule
     *
     * @return \Core\Lib\Data\Container\Field
     */
    public function setValidation($rule)
    {
        if (! is_array($rule)) {
            $rule = (array) $rule;
        }

        $this->validate = $rule;

        return $this;
    }

    /**
     * Adds validation rule to the rules stack
     *
     * @param string|array $rule
     *            Validation rule
     *
     * @return \Core\Lib\Data\Container\Field
     */
    public function addValidation($rule)
    {
        if (is_array($rule)) {
            foreach ($rule as $val) {
                $this->validate[] = $val;
            }
        }
        else {
            $this->validate[] = $rule;
        }

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
     *            Flag to serialize the fields value
     *
     * @return \Core\Lib\Data\Container\Field
     */
    public function setSerialize($serialize)
    {
        $this->serialize = (bool) $serialize;

        return $this;
    }

    /**
     * Set filter statements for the field while overwriting existing filter rules
     *
     * @param string|array $filter
     *
     * @return \Core\Lib\Data\Container\Field
     */
    public function setFilter(array $filter)
    {
        if (! is_array($filter)) {
            $filter = (array) $filter;
        }

        $this->filter = $filter;

        return $this;
    }

    /**
     * Adds one or mor filter to the fields filter stack
     *
     * @param string|array $filter
     *
     * @return \Core\Lib\Data\Container\Field
     */
    public function addFilter($filter)
    {
        if (is_array($filter)) {
            foreach ($filter as $val) {
                $this->filter[] = $val;
            }
        }
        else {
            $this->filter[] = $filter;
        }

        return $this;
    }

    /**
     * Returns the fields set filters
     *
     * @return array
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Filters the fields value by using the set filter statements
     *
     * It is possible to filter the field with multiple filters.
     * This method uses filter_var_array() to filter the value.
     *
     * @return \Core\Lib\Data\Container\Field
     */
    public function filter()
    {
        if (! empty($this->filter)) {

            $var = [
                'data' => $this->value
            ];

            foreach ($this->filter as $filter) {

                $args = [
                    'data' => $filter
                ];

                $this->value = filter_var_array($var, $args)['data'];
            }
        }

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
    public function count()
    {
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
     *
     * @see ArrayAccess::offsetSet()
     *
     * @throws FieldException
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            Throw new FieldException('Anonymous data field access is not allowed. Provide a field name.');
        }
        else {

            if (property_exists(__CLASS__, $offset)) {
                $this->$offset = $value;
            }
            else {
                $this->custom_properties[$offset] = $value;
            }
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        if (isset($this->$offset)) {
            return ! empty($this->$offset);
        }

        if (isset($this->custom_properties[$offset])) {
            return ! empty($this->custom_properties[$offset]);
        }

        return false;
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        if (isset($this->$offset)) {
            unset($this->$offset);
            return;
        }

        if (isset($this->custom_properties[$offset])) {
            unset($this->custom_properties[$offset]);
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetGet()
     *
     * @throws FieldException
     */
    public function offsetGet($offset)
    {
        if (isset($this->$offset)) {
            return $this->$offset;
        }
        elseif (isset($this->custom_properties[$offset])) {
            return $this->custom_properties[$offset];
        }
        else {
            Throw new FieldException('Field property "' . $offset . '" does not exists.');
        }
    }
}
