<?php
namespace Core\Lib\Language;

use Core\Lib\Errors\Exceptions\InvalidArgumentException;
use Core\Lib\Traits\ArrayTrait;
use Core\Lib\Errors\Exceptions\RuntimeException;

/**
 * Language.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Language
{

    use ArrayTrait;

    private $data = [];

    /**
     * Loads an app related language file
     *
     * When text of an app already exists, the loaded texts are merged into the existing array.
     *
     * @param string $app_name
     *            Name of app the file is to load from
     * @param string $lang_file
     *            Name of the languagefile to load
     *
     * @throws InvalidArgumentException
     */
    public function loadLanguageFile($app_name, $lang_file)
    {
        if (file_exists($lang_file)) {

            $lang_array = include ($lang_file);

            if (is_array($lang_array)) {

                $lang_array = $this->arrayFlatten($lang_array, '', '.', true);

                if (array_key_exists($app_name, $this->data)) {
                    $this->data[$app_name] = array_merge($this->data[$app_name], $lang_array);
                }
                else {
                    $this->data[$app_name] = $lang_array;
                }
            }
        }
    }

    /**
     * Returns translated text
     *
     * Only translates texts that does not contain spaces.
     * Tries to find core text when either app language or the requested key is found.
     * Returns the key when no language text is found.
     * Allows linking of keys and throws an exception when it comes to ininite loops.
     *
     * @param string $key
     *            Key of the requested text
     * @param string $app
     *            Optional app name the key belongs to. (Default: 'Core')
     *
     * @throws RuntimeException
     *
     * @return string
     */
    public function getText($key, $app = 'Core')
    {

        // IMPORTANT! Keys with spaces won't be processed without any further
        // notice to the developer or user. Spaces mean texts and no keys.
        if (is_array($key) || strpos($key, ' ')) {
            return $key;
        }

        // Return key when key is not found
        if (! isset($this->data[$app]) || ! isset($this->data[$app][$key])) {

            if (isset($this->data['Core'][$key])) {
                return $this->data['Core'][$key];
            }

            return $key;
        }

        $text = $this->data[$app][$key];

        // Prevent infinite loops
        if ($text == $key) {
            Throw new RuntimeException(sprintf('There is an infinite loop recursion in language data of app "%s" on key "%s"', $app, $key));
        }

        // Return requested text
        return $this->getText($text, $app);
    }
}
