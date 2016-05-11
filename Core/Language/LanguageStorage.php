<?php
namespace Core\Language;

use Core\Storage\Storage;

/**
 * LanguageStorage.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class LanguageStorage extends Storage
{

    public function getText($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return $key;
    }

    public function text($key)
    {
        return $this->getText($key);
    }

    public function get($key)
    {
        return $this->getText($key);
    }
}

