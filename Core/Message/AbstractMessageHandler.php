<?php
namespace Core\Message;

/**
 * AbstractMessageHandler.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class AbstractMessageHandler implements \IteratorAggregate
{

    /**
     *
     * @var StorageInterface
     */
    private $storage;

    /**
     *
     * {@inheritDoc}
     *
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new \IteratorIterator($this->storage);
    }

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
    final public function add(MessageInterface $msg)
    {
        $this->storage->add($msg);
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

    /**
     * Creates and returns a message object
     *
     * @param boolean $autoadd
     *            Optional flag to dis-/enable autoadd of message to the message storage. (Default: true)
     *
     * @return \Core\Message\Message
     */
    final public function &factory($autoadd = true)
    {
        $msg = new Message();

        if ($autoadd == true) {
            $this->storage->add($msg);
        }

        return $msg;
    }
}
