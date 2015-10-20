<?php
namespace Core\Lib\Content;

use Core\Lib\Errors\Exceptions\InvalidArgumentException;

/**
 * Language.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Language
{

    private $txt = [];

    /**
     * Loads an app related language file.
     *
     * Throws exception when the language file cannot be found.
     *
     * @param string $app_name
     * @param string $lang_file
     *
     * @throws InvalidArgumentException
     */
    public function loadLanguageFile($app_name, $lang_file)
    {
        if ( file_exists($lang_file)) {
            $this->txt[$app_name] = include ($lang_file);
        }
    }

    /**
     * Returns translated text
     *
     * Only translates texts that does not contain spaces.
     * Tries to find core text when either app language or the requested key is found.
     * Returns the key when no language text is found.
     *
     * @param string $key
     * @param string $app
     *
     * @return string
     */
    public function getTxt($key, $app = 'core')
    {
        // IMPORTANT! Keys with spaces won't be processed without any further
        // notice to the developer or user. Spaces mean texts and no keys for the $txt array.
        if (strpos($key, ' ')) {
            return $key;
        }

        // Return key when key is not found
        if (! isset($this->txt[$app]) || ! isset($this->txt[$app][$key])) {

            if (isset($this->txt['Core'][$key])) {
                return $this->txt['Core'][$key];
            }

            return $key;
        }

        // Return requested text
        return $this->txt[$app][$key];
    }
}
