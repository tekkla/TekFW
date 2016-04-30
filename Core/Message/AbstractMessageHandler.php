<?php
namespace Core\Message;

/**
 * AbstractMessageHandler.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class AbstractMessageHandler
{

    /**
     *
     * @var StorageInterface
     */
    private $storage;

    /**
     *
     * @param StorageInterface $storage
     */
    final public function setStorage(StorageInterface &$storage)
    {
        $this->storage = &$storage;
    }

    /**
     * Adds a value to the storage
     *
     * @param string $key
     *            Id key of the element
     * @param mixed $value
     *            The value to store
     */
    final public function add($key, $value)
    {
        $this->storage->add($key, $value);
    }

    /**
     * Returns a value from storage searched by it' key
     *
     * @param string $key
     *            Id of element in storage
     */
    final public function get($key)
    {
        return $this->storage->get($key);
    }


    /**
     * Returns all elements in stored as array
     *
     * @return array of all stored elements
     */
    final public function getAll()
    {
        return $this->storage->getAll();
    }

    /**
     * Clears the the storage content
     */
    final public function clear()
    {
        $this->storage->clear();
    }

    public function createMessage()
    {
        return new Message();
    }

}