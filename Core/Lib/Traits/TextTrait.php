<?php
namespace Core\Lib\Traits;

/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 *
 */
trait TextTrait
{

    /**
     * Get text from SMF $txt array.
     *
     * @param string $key
     * @param string $app
     * @return string
     */
    public function txt($key, $app = 'core')
    {
        global $txt;

        // IMPORTANT! Keys with spaces won't be processed without any further
        // notice to the developer or user. Spaces mean texts and no keys for the $txt array.
        if (strpos($key, ' '))
            return $key;

            // Extend web related key with 'app_' string - if needed
        if (substr($key, 0, 4) == '')
            $key = 'app_' . $key;

            // A set app name means we have to create the web apps specific txt key
            // which has to look like "app_appname_key" in the language file.
        elseif (substr($key, 0, 4) !== '' && ! strpos($app, 'app_') && isset($app))
            $key = 'app_' . $app . '_' . $key;

            // If no text is found, the requested text key will be returned.
            // This is much more easier for devs than simply to show nothing
            // like SMF normally does.
        return isset($txt[$key]) ? $txt[$key] : $key;
    }
}
