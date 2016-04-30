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
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new StorageIterator($this->storage);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Message\StorageInterface::add()
     */
    public function add(MessageInterface $value)
    {
        if (empty($this->storage)) {
            $this->storage = [];
        }
        
        $this->storage[] = $value;
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
