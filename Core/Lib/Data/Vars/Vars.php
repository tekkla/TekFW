<?php
namespace Core\Lib\Data;

use Core\Lib\Data\Vars\VarsException;
/**
 * Vars.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Vars implements \IteratorAggregate, \ArrayAccess
{

    /**
     * Vars storage
     *
     * @var array
     */
    private $vars = [];

    /**
     * Access on var
     *
     * @param string $name
     *            Name of var
     *
     * @return mixed
     */
    public function &__get($name)
    {
        $null = null;

        if (isset($this->vars[$name])) {
            return $this->vars[$name];
        }
        else {
            return $null;
        }
    }

    /**
     * Unsets var.
     *
     * Without $name parameter set, all stored vars will be unset.
     *
     * @param string $name
     */
    public function __unset($name = null)
    {
        if (empty($name)) {
            $this->vars = [];
            return;
        }

        if (isset($this->vars[$name])) {
            unset($this->vars[$name]);
        }
    }

    /**
     * Isset on vars
     *
     * @param string $name
     */
    public function __isset($name)
    {
        return isset($this->vars[$name]) && empty($this->vars[$name]);
    }

    /**
     * Return iterator
     *
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->vars);
    }

    /**
     * Returns all stored vars
     *
     * @return array
     */
    public function get()
    {
        return $this->vars;
    }

    /**
     * Returns array with the names of all stored vars
     *
     * @return array
     */
    public function getVarNames()
    {
        return array_keys($this->vars);
    }

    /**
     * Returns a reference to a var
     *
     * @param string $var
     *            Name of var
     *
     * @return mixed
     */
    public function &getVarByRef($var)
    {
        return $this->vars[$var];
    }

    /**
     * Returns value of a var byVal
     *
     * @param string $var
     *            Name of var
     *
     * @return mixed
     *
     */
    public function getVarByVal($var)
    {
        return $this->vars[$var];
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
        if (empty($offset)) {
            Throw new VarsException('You can not add an anonymous var.');
        }

        $this->vars[$offset] = $value;
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        if (! isset($this->vars[$offset])) {
            return false;
        }

        return empty($this->vars[$offset]) ? false : true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        if (isset($this->vars[$offset])) {
            unset($this->vars[$offset]);
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetGet()
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->vars[$offset]) ? $this->vars[$offset] : null;
    }
}
