<?php
namespace Core\Lib\Traits;

use Core\Lib\Errors\Exceptions\InvalidArgumentException;

/**
 * StringTrait.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
trait StringTrait
{

    /**
     * Shortens a string to the given length and adds '...' at the end of string
     *
     * @param string $string
     * @param int $length
     * @param string $addition
     * @return string
     */
    protected function stringShorten($string, $length, $addition = ' [...]', $wrap_url = '')
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
    protected function stringCamelize($string, $upper_first = true)
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
    protected function stringUncamelize($string)
    {
        if (empty($string)) {
            Throw new InvalidArgumentException('The string set to be uncamelized is empty.', 1000);
        }

        if (! is_string($string)) {
            Throw new InvalidArgumentException('Only strings can be used to be uncamelized.', 1000);
        }

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
    protected function stringNormalize($string)
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
    protected function stringIsSerialized($value, &$result = null)
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
}

