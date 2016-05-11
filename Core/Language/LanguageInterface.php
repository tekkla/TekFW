<?php
namespace Core\Language;

/**
 * LanguageInterface.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
interface LanguageInterface
{

    /**
     * Returns a string from language storage
     *
     * Returns the $key when no matching element is found.
     *
     * @param string $storage_name
     *            The name of the language storage to query
     * @param string $key
     *            The key we are looking for in the storage
     *
     * @return string
     */
    public function get($storage_name, $key);
}

