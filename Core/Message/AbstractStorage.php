<?php
namespace Core\Message;

abstract class AbstractStorage
{
    /**
     *
     * @var unknown
     */
    protected $storage;

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Message\StorageInterface::setStorage()
     */
    public function setStorage(&$storage)
    {
        $this->storage = &$storage;
    }
}
