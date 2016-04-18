<?php
namespace Core\Traits;

use Core\Errors\CoreException;

/**
 * ArrayTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
trait ArrayTrait
{

    /**
     * Inserts an array ($insert) at ($position) after the key ($search) in an array ($array) and returns a combined
     * array
     *
     * @param array $array
     *            Array to insert the array
     * @param array $insert
     *            Array to insert inti $array
     * @param string $search
     *            Key to search and insert after
     * @param number $position
     *            Position after the found key to insert into
     *
     * @throws InvalidArgumentException
     *
     * @return array
     */
    function arrayInsertArrayAfter(&$array, $search, $insert, $position = 0)
    {
        if (! is_array($array)) {
            throw new CoreException('Wrong parameter type.', 1000);
        }

        $counter = 0;
        $keylist = array_keys($array);

        foreach ($keylist as $key) {
            if ($key == $search) {
                break;
            }
            $counter ++;
        }

        $counter += $position;

        $array = array_slice($array, 0, $counter, true) + $insert + array_slice($array, $counter, null, true);

        return $array;
    }

    /**
     * Slices an array at the search point and returns both slices.
     *
     * @param array $array
     * @param string $search
     * @param number $position
     *
     * @throws InvalidArgumentException
     *
     * @return array
     */
    function arrayGetSlicesByKey($array, $search, $position = 0)
    {
        if (! is_array($array)) {
            throw new CoreException('Wrong parameter type.', 1000);
        }

        $counter = 0;
        $keylist = array_keys($array);

        foreach ($keylist as $key) {
            if ($key == $search) {
                break;
            }

            $counter ++;
        }

        $counter += $position;

        return [
            array_slice($array, 0, $counter, true),
            array_slice($array, $counter, null, true)
        ];
    }

    /**
     * Checks a value whether to be an array, if its empty and when not an empty array if it's an associative one.
     *
     * @param array $array
     *            The array to check
     *
     * @throws InvalidArgumentException
     *
     * @return boolean
     */
    function arrayIsAssoc($array)
    {
        if (! is_array($array)) {
            Throw new CoreException('ArrayTrait::arrayIsAssoc() : You can only check arrays to be associative.');
        }

        if (empty($array)) {
            return false;
        }

        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }

    /**
     * Flattens a multidimensional array
     *
     * @param array $array
     *            The array to flatten
     * @param string $glue
     *            Optional glue to get flattened array with this glue as return value
     * @param boolean $preserve_flagged_arrays
     *            With this optional flag and a set __preserve key in the array the array will be still flattended but
     *            also be stored as array with an ending .array key. Those arrays will not be flattened further more.
     *            This means any nesting array will stay arrays in this array.
     *
     * @return string|array
     */
    function arrayFlatten(array $array, $prefix = '', $glue = '.', $preserve_flagged_arrays = false)
    {
        $result = [];

        foreach ($array as $key => $value) {

            // Subarrray handling needed?
            if (is_array($value)) {

                // __preserve key set tha signals us to store the array as it is?
                if ($preserve_flagged_arrays && array_key_exists('__preserve', $value)) {
                    $result[$prefix . $key . $glue . 'array'] = $value;
                    unset($value['__preserve']);
                }

                // Flatten the array
                $result = $result + $this->arrayFlatten($value, $prefix . $key . $glue, $glue, $preserve_flagged_arrays);
            }
            else {
                $result[$prefix . $key] = $value;
            }
        }
        return $result;
    }

    /**
     * Searches an $array recursively for the $key and returns all matching values as array
     *
     * @param array $array
     *            Array to search in
     * @param string $key
     *            The key compare
     * @param string $search
     *            The value to search for in key
     *
     * @return array
     */
    function arraySearchValuesByKey(array $array, $key, $search)
    {
        $it = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));

        $out = [];

        foreach ($it as $sub) {

            $sub_array = $it->getSubIterator();

            if ($sub_array[$key] === $search) {
                $out[] = iterator_to_array($sub_array);
            }
        }

        return $out;
    }

    /**
     * Creates an multidimensional array out of an array with keynames
     *
     * @param array $arr
     *            Reference to the array to fill
     * @param array $keys
     *            The array holding the keys to use as values
     * @param mixed $value
     *            The value to assign to the last key
     */
    function arrayAssignByKeys(array &$arr, array $keys, $value)
    {
        foreach ($keys as $key) {
            $arr = &$arr[$key];
        }

        $arr = $value;
    }

    /**
     * Creates an multidimensional array out of an string notation and adds a value to the last element.
     *
     * @param array $arr
     *            Reference to the array to fill
     * @param string $path
     *            Path to create array from
     * @param mixed $value
     *            The value to assign to the last key
     * @param string $separator
     *            Optional seperator to be used while splittint the path into key values (Default: '.')
     */
    function arrayAssignByPath(&$arr, $path, $value, $separator = '.')
    {
        $keys = explode($separator, $path);

        foreach ($keys as $key) {
            $arr = &$arr[$key];
        }

        $arr = $value;
    }
}
