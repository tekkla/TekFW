<?php
namespace Core\Storage;

/**
 * StorageInterface.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
interface StorageInterface
{

    /**
     * Returns the value by key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getValue($key);

    /**
     * Sets a value by key
     *
     * @param string $key
     * @param mixed $value
     */
    public function setValue($key, $value);

    /**
     * Same as getValue() only shorter
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * Same as setValue() only the short version
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value);

    /**
     * Checks for existance of an element by key
     *
     * @param string $key
     *
     * @return boolean
     */
    public function exists($key);

    /**
     * Magic method version of getValue()
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key);

    /**
     * Magic method version of setValue()
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value);

    /**
     * Magic method version of exists()
     *
     * @param string $key
     *
     * @return boolean
     */
    public function __isset($key);
}

