<?php
namespace Core\Language;

/**
 * Text.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Text
{

    /**
     *
     * @var string
     */
    private $storage_name;

    /**
     *
     * @var LanguageInterface
     */
    private $language;

    /**
     *
     * @param LanguageInterface $language
     */
    public function setLanguage(LanguageInterface $language)
    {
        $this->language = $language;
    }

    /**
     * Sets the name of the language storage to query
     *
     * @param string $storage_name
     */
    public function setStorageName($storage_name)
    {
        $this->storage_name = $storage_name;
    }

    /**
     * Queries language storage for a string mapped to a key.
     *
     * @param string $key
     */
    public function get($key)
    {
        if (! isset($this->language)) {
            Throw new LanguageException('Text neeeds a set Language object.');
        }

        if (! isset($this->storage_name)) {
            Throw new LanguageException('No storage name set.');
        }

        return $this->language->get($this->storage_name, $key);
    }
}
