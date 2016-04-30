<?php
namespace Core\Message;

/**
 * MessageStorage.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class MessageStorage implements StorageInterface
{

    private $storage;

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Message\StorageInterface::add()
     */
    public function add($key, $value)
    {
        if (empty($this->storage)) {
            $this->storage = [];
        }

        $this->storage[$key] = $value;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Message\StorageInterface::clear()
     */
    public function clear()
    {
        $this->storage = [];
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Message\StorageInterface::get()
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->storage)) {
            return $this->storage[$key];
        }
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Message\StorageInterface::getAll()
     */
    public function getAll()
    {
        return $this->storage;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Message\StorageInterface::setStorage()
     */
    public function setStorage(array &$storage)
    {
        $this->storage = &$storage;
    }
}
