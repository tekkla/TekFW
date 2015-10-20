<?php
namespace Core\Lib\Traits;

/**
 * ConvertTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
trait ConvertTrait
{

    /**
     * Converts an object and it's public members recursively into an array.
     * Use this if you want to convert objects into array.
     *
     * @param object $obj
     * @return array
     */
    function convertObjectToArray($obj)
    {
        if (! is_object($obj)) {
            return $obj;
        }

        $out = [];

        foreach ($obj as $key => $val) {
            if (is_object($val)) {
                $out[$key] = $this->convertToArray($val);
            }
            else {
                $out[$key] = $val;
            }
        }

        return $out;
    }

    /**
     * Converts a value into boolean.
     *
     * Converts the following strings to true: true
     *
     * @param mixed $value
     *
     * @return boolean
     */
    function convertToBool($value)
    {
        if (! is_string($value)) {
            return (bool) $value;
        }

        switch (strtolower($value)) {
            case '1':
            case 'true':
            case 'on':
            case 'yes':
            case 'y':
                return true;
            default:
                return false;
        }
    }
}
