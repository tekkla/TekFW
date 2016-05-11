<?php
namespace Core\Language;

use function Core\arrayFlatten;

/**
 * Language.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Language implements LanguageInterface
{
    /**
     *
     * @var array
     */
    private $data=[];

    /**
     *
     * @var string
     */
    private $fallback = '';

    /**
     * Sets the name of the fallback storage
     *
     * The fallback storage will be queried for a text when the requested storage returns nor result.
     *
     * @param string $fallback
     */
    public function setFallbackStorageName($fallback)
    {
        $this->fallback = $fallback;
    }

    /**
     * Loads a language file
     *
     * When a text already exists in a storage, the loaded texts overwrites and extends the storage with the new data.
     *
     * @param string $storage_name
     *            Name of storage the loaded language texts will be stored in
     * @param string $filename
     *            File path of mthe languagefile to load
     *
     * @throws InvalidArgumentException
     */
    public function loadLanguageFile($storage_name, $filename)
    {
        if (file_exists($filename)) {

            $lang_array = include ($filename);

            if (is_array($lang_array)) {

                $lang_array = arrayFlatten($lang_array, '', '.', true);

                foreach ($lang_array as $key => $val) {

                    if (! array_key_exists($storage_name, $this->data)) {
                        $this->data[$storage_name] = new LanguageStorage();
                    }

                    $this->data[$storage_name]->{$key} = $val;
                }
            }
        }
    }

    /**
     * Returns text form by key from a storage
     *
     * Only translates texts that does not contain spaces.
     * Tries to find text in optional set fallback storage when key is not found in the requested one.
     * Returns the key when no text is found.
     * Allows linking of keys within a storage and throws an exception when it comes to ininite loops.
     *
     * @param string $key
     *            Key of the requested text
     * @param string $storage_name
     *            Name of storage to query for the key belongs to.
     *
     * @throws LanguageException
     *
     * @return string
     */
    public function get($storage_name, $key)
    {

        // IMPORTANT! Keys with spaces won't be processed without any further
        // notice to the developer or user. Spaces mean texts and no keys.
        if (is_array($key) || strpos($key, ' ')) {
            return $key;
        }

        // Return key when key is not found
        if (empty($this->data[$storage_name]) || ! isset($this->data[$storage_name]->{$key})) {

            // Do we have a fallback storage set to look for?
            if (! empty($this->fallback) && isset($this->data[$this->fallback]))

                if (isset($this->data[$this->fallback]->{$key})) {
                    return $this->data[$this->fallback]->{$key};
                }

            return $key;
        }

        $text = $this->data[$storage_name]->{$key};

        // Prevent infinite loops
        if ($text == $key) {
            Throw new LanguageException(sprintf('There is an infinite loop recursion in language data ins storage "%s" on key "%s"', $storage_name, $key));
        }

        // Return requested text
        return $this->get($storage_name, $text);
    }

    /**
     * Returns a reference to the language string of a specific app
     *
     * @param string $storage_name
     *            Name of the app the language blongs to
     *
     * @return AppLanguage
     */
    public function &getAppLanguage($storage_name)
    {
        $return = false;

        if (array_key_exists($storage_name, $this->data)) {
            $return = $this->data[$storage_name];
        }

        return $return;
    }

    public function createTextAdapter($storage_name)
    {
        $adapter = new Text();
        $adapter->setLanguage($this);
        $adapter->setStorageName($storage_name);

        return $adapter;
    }
}
