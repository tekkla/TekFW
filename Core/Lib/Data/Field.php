<?php
namespace Core\Lib\Data;

use Core\Lib\Errors\Exceptions\InvalidArgumentException;

/**
 * Field.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
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
     * Constructor
     */
    public function __construct()
    {}

    /**
     * On echo field .
     *
     *
     *
     * ..
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
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
     * @throws InvalidArgumentException
     *
     * @return \Core\Lib\Data\Field
     */
    public function setSize($size)
    {
        $size = (int) $size;

        if (empty($size)) {
            Throw new InvalidArgumentException('field size cannot be zero.');
        }

        if (! is_numeric($size)) {
            Throw new InvalidArgumentException('Only numbers are allowed as field size.');
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
     * Returns field value.
     *
     * Same as getValue() only shorter.
     *
     * @return mixed
     */
    public function get()
    {
        return $this->value;
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
    public function setValue($value, $type = null)
    {
        // Is the data serialized?
        if ($this->isSerialized($value)) {
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
     * Same as setValue only shorter.
     *
     * Takes care of serialized data.
     *
     * @param mixed $value
     *
     * @return \Core\Lib\Data\Field
     */
    public function set($value, $type = null)
    {
        // Is the data serialized?
        if ($this->isSerialized($value)) {
            $this->serialize = true;
            $value = unserialize($value);
        }

        $this->value = $value;

        if (isset($type)) {
            $this->setType($type);
        }

        return $this;
    }

    /**
     * Sets the control to use when field used in displayfunctions.
     *
     * @param string $control_type
     *
     * @return \Core\Lib\Data\Field
     */
    public function setControl($control_type)
    {
        $this->control = $control_type;

        return $this;
    }

    /**
     * Get control type.
     *
     * @retur nstring
     */
    public function getControl()
    {
        return $this->control;
    }

    /**
     * Sets fields default value.
     *
     * @param mixed $default_value
     *
     * @return \Core\Lib\Data\Field
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
     * Get control default value.
     *
     * @retur nstring
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Get validation rules.
     *
     * @return array
     */
    public function getValidation()
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
        if (! is_array($rule)) {
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
     * @throws InvalidArgumentException
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            Throw new InvalidArgumentException('Anonymous data field access is not allowed. Provide a field name.');
        }
        else {
            $this->$offset = $value;
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset) && ! empty($this->$offset);
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetGet()
     *
     * @throws InvalidArgumentException
     */
    public function offsetGet($offset)
    {
        if (isset($this->$offset)) {
            return $this->$offset;
        }
        else {
            Throw new InvalidArgumentException('Field property "' . $offset . '" does not exists.');
        }
    }
}
