<?php
namespace Core\Lib\Content;

/**
 *
 * @author Michael
 *
 */
class Language
{
	private static $txt = [];

	/**
	 * Loads an app related language file. Throws exception when the language file cannot be found.
	 *
	 * @param string $app_name
	 * @param string $lang_file
	 *
	 * @throws \InvalidArgumentException
	 */
	public function loadLanguageFile($app_name, $lang_file)
	{
		if (!file_exists($lang_file)) {
			throw new \InvalidArgumentException('The language file "' . $lang_file . '" does not exist.');
		}

		self::$txt[$app_name] = include($lang_file);
	}

	public function getTxt($key, $app='core')
	{
		// IMPORTANT! Keys with spaces won't be processed without any further
		// notice to the developer or user. Spaces mean texts and no keys for the $txt array.
		if (strpos($key, ' '))
			return $key;

		// Return key when now app related texts found
		if (!isset(self::$txt[$app]))
			return $key;

		// Return key when key is not found
		if (!isset(self::$txt[$app][$key]))
			return $key;

		// Return requested text
		return self::$txt[$app][$key];
	}
}
