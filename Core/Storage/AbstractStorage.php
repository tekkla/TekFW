<?php
namespace Core\Storage;

/**
 * AbstractStorage.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
abstract class AbstractStorage implements StorageInterface, \ArrayAccess, \IteratorAggregate
{

    protected $data = [];

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Storage\AbstractStorage::getValue()
     */
    public function getValue($key)
    {
        if (! isset($this->data[$key])) {
            $this->data[$key] = new self();
        }

        return $this->data[$key];
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Storage\StorageInterface::setValue()
     */
    public function setValue($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Storage\StorageInterface::get()
     */
    public function get($key)
    {
        return $this->getValue($key);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Storage\StorageInterface::set()
     */
    public function set($key, $value)
    {
        $this->setValue($key, $value);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Storage\StorageInterface::__get()
     */
    public function __get($key)
    {
        return $this->getValue($key);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Storage\StorageInterface::__set()
     */
    public function __set($key, $value)
    {
        $this->setValue($key, $value);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Storage\StorageInterface::exists()
     */
    public function exists($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Storage\StorageInterface::__isset()
     */
    public function __isset($offset)
    {
        return $this->exists($offset);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return $this->exists($offset);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        return $this->getValue($offset);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        $this->setValue($offset, $value);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        if ($this->exists($offset)) {
            unset($this->data[$offset]);
        }
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }
}

