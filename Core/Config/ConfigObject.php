<?php
namespace Core\Config;

/**
 * ConfigObject.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class ConfigObject
{

    public $storage;

    public $id;

    public $value;

    public function setStorage($storage)
    {
        $this->storage = $storage;
    }

    public function getStorage()
    {
        return $this->storage;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}
