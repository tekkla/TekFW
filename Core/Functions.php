<?php
namespace Core;

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
        throw new \Exception('Wrong parameter type.', 1000);
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
        throw new \Exception('Wrong parameter type.', 1000);
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
        Throw new \Exception('ArrayTrait::arrayIsAssoc() : You can only check arrays to be associative.');
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
            $result = $result + arrayFlatten($value, $prefix . $key . $glue, $glue, $preserve_flagged_arrays);
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

/**
 * Shortens a string to the given length and adds '...' at the end of string
 *
 * @param string $string
 * @param int $length
 * @param string $addition
 * @return string
 */
function stringShorten($string, $length, $addition = ' [...]', $wrap_url = '')
{
    // Shorten only what is longer than the length
    if (strlen($string) < $length) {
        return $string;
    }

    // Shorten string by length
    $string = substr($string, 0, $length);

    // Shorten further until last occurence of a ' '
    $string = substr($string, 0, strrpos($string, ' '));

    if (! empty($wrap_url)) {
        $addition = '<a href="' . $wrap_url . '">' . $addition . '</a>';
    }

    // Add addition
    $string .= $addition;

    // Done.
    return $string;
}

/**
 * Converts stings with underscores into case sensitiv strings.
 * Important: This Converter is one of the most important in the complete framework
 * because it's used on each request for getting the names of app, controller
 * and function.
 *
 * @param string $val
 * @return string
 * @tutorial <code>
 *           <?php
 *           $string = 'my_name';
 *           $string = String::camelize($string);
 *           echo $string;
 *           </code>
 *           Result: MyName
 */
function stringCamelize($string, $upper_first = true)
{
    // even if there is no underscore in string, the first char will be converted to uppercase
    if (strpos($string, '_') == 0 && $upper_first == true) {
        $string = ucwords($string);
    }
    else {
        $string = str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($string))));

        if ($upper_first == false) {
            $string = lcfirst($string);
        }
    }

    return $string;
}

/**
 * Converts camel case strings into underscored strings.
 * Important: This Converter is one of the most important in the complete framework
 * because it's used on each request for getting the names of app, controller
 * and function.
 *
 * @param string $val
 *
 * @throws InvalidArgumentException#
 *
 * @return string Example:
 *         IN: MyName | OUT: my_name
 *         IN: ThisIsATest | OUT: this_is_a_test
 */
function stringUncamelize($string)
{
    if (empty($string)) {
        Throw new \Exception('The string set to be uncamelized is empty.', 1000);
    }

    if (! is_string($string)) {}

    // set first letter to lowercase
    $string[0] = strtolower($string[0]);

    $callback = function ($c) {
        return '_' . strtolower($c[1]);
    };

    // replace all other with _{letter}
    $string = preg_replace_callback('/([A-Z])/', $callback, $string);

    $string = trim(preg_replace('@[_]{2,}@', '_', $string), '_');

    return $string;
}

/**
 * Normalizes a string
 *
 * @param string $string
 * @return string
 */
function stringNormalize($string)
{
    $table = array(
        'Š' => 'S',
        'š' => 's',
        'Đ' => 'Dj',
        'đ' => 'dj',
        'Ž' => 'Z',
        'ž' => 'z',
        'Č' => 'C',
        'č' => 'c',
        'Ć' => 'C',
        'ć' => 'c',
        'À' => 'A',
        'Á' => 'A',
        'Â' => 'A',
        'Ã' => 'A',
        'Ä' => 'Ae',
        'Å' => 'A',
        'Æ' => 'A',
        'Ç' => 'C',
        'È' => 'E',
        'É' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'Ì' => 'I',
        'Í' => 'I',
        'Î' => 'I',
        'Ï' => 'I',
        'Ñ' => 'N',
        'Ò' => 'O',
        'Ó' => 'O',
        'Ô' => 'O',
        'Õ' => 'O',
        'Ö' => 'Oe',
        'Ø' => 'Oe',
        'Ù' => 'U',
        'Ú' => 'U',
        'Û' => 'U',
        'Ü' => 'Ue',
        'Ý' => 'Y',
        'Þ' => 'B',
        'ß' => 'Ss',
        'à' => 'a',
        'á' => 'a',
        'â' => 'a',
        'ã' => 'a',
        'ä' => 'ae',
        'å' => 'a',
        'æ' => 'a',
        'ç' => 'c',
        'è' => 'e',
        'é' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'ì' => 'i',
        'í' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ð' => 'o',
        'ñ' => 'n',
        'ò' => 'o',
        'ó' => 'o',
        'ô' => 'o',
        'õ' => 'o',
        'ö' => 'oe',
        'ø' => 'oe',
        'ù' => 'u',
        'ú' => 'u',
        'û' => 'u',
        'ü' => 'ue',
        'ý' => 'y',
        'ý' => 'y',
        'þ' => 'b',
        'ÿ' => 'y',
        'Ŕ' => 'R',
        'ŕ' => 'r'
    );

    $string = strtr($string, $table);

    return $string;
}

/**
 * Tests if an input is valid PHP serialized string
 *
 * Checks if a string is serialized using quick string manipulation
 * to throw out obviously incorrect strings. Unserialize is then run
 * on the string to perform the final verification.
 *
 * Valid serialized forms are the following:
 * <ul>
 * <li>boolean: <code>b:1;</code></li>
 * <li>integer: <code>i:1;</code></li>
 * <li>double: <code>d:0.2;</code></li>
 * <li>string: <code>s:4:"test";</code></li>
 * <li>array: <code>a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}</code></li>
 * <li>object: <code>O:8:"stdClass":0:{}</code></li>
 * <li>null: <code>N;</code></li>
 * </ul>
 *
 * @param string $value
 *            test for serialized form
 * @param mixed $result
 *            unserialize() of the $value
 *
 * @return boolean if $value is serialized data, otherwise false
 */
function stringIsSerialized($value, &$result = null)
{
    // Bit of a give away this one
    if (! is_string($value)) {
        return false;
    }

    // Empty strings cannot get unserialized
    if (strlen($value) <= 1) {
        return false;
    }

    // Serialized false, return true. unserialize() returns false on an
    // invalid string or it could return false if the string is serialized
    // false, eliminate that possibility.
    if ($value === 'b:0;') {
        $result = false;
        return true;
    }

    $length = strlen($value);
    $end = '';

    switch ($value[0]) {
        case 's':
            if ($value[$length - 2] !== '"') {
                return false;
            }
        case 'b':
        case 'i':
        case 'd':
            $end .= ';';
        case 'a':
        case 'O':
            $end .= '}';

            if ($value[1] !== ':') {
                return false;
            }

            switch ($value[2]) {
                case 0:
                case 1:
                case 2:
                case 3:
                case 4:
                case 5:
                case 6:
                case 7:
                case 8:
                case 9:
                    break;

                default:
                    return false;
            }
        case 'N':
            $end .= ';';

            if ($value[$length - 1] !== $end[0]) {
                return false;
            }

            break;

        default:
            return false;
    }

    if (($result = @unserialize($value)) === false) {
        $result = null;
        return false;
    }

    return true;
}

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
            $out[$key] = convertObjectToArray($val);
        }
        else {
            $out[$key] = $val;
        }
    }

    return $out;
}

/**
 * Converts a value into boolean
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