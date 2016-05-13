<?php
namespace Core\Storage;

/**
 * Storage.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Storage extends AbstractStorage
{
    /**
     * {@inheritDoc}
     * @see \Core\Storage\AbstractStorage::getValue()
     */
    public function getValue($key)
    {
        if (!isset($this->data[$key])) {
            $this->data[$key] = new self();
        }

        return $this->data[$key];

    }
}
