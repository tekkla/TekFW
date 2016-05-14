<?php
namespace Core\Data;

class DataObject implements DataObjectInterface, \ArrayAccess
{

    public function __get($key)
    {
        Throw new DataException(sprintf('Field "%s" does not exist in this DataObject', $key));
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        if (! property_exists($this, $offset)) {
            Throw new DataException(sprintf('Field "%s" does not exist in this DataObject', $offset));
        }

        return $this->{$offset};
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        if (property_exists($this, $offset)) {
            unset($this->{$offset});
        }
    }
}
