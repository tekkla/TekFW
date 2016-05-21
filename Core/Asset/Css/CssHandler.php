<?php
namespace Core\Asset\Css;

use Core\Asset\AbstractAssetHandler;

/**
 * CssHandler.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class CssHandler extends AbstractAssetHandler
{

    /**
     * This is a css handler
     *
     * @var string
     */
    protected $type = self::TYPE_CSS;

    /**
     * Creates and returns a link css object.
     *
     * @param string $url
     *
     * @return CssObject
     */
    public function &createLink($url)
    {
        $css = new CssObject();

        $css->setType($css::TYPE_FILE);
        $css->setContent($url);

        $this->addObject($css);

        return $css;
    }

    /**
     * Creates and returns an inline css object
     *
     * @param string $styles
     *
     * @return CssObject
     */
    public function &createInline($styles)
    {
        $css = new CssObject();

        $css->setType($css::TYPE_INLINE);
        $css->setContent($styles);

        $this->addObject($css);

        return $css;
    }

    /**
     * Returns
     */
    public function getContent()
    {
        if (empty($this->objects)) {
            return '';
        }

        $files = [];
        $local_files = [];
        $inline = [];

        /* @var $css CssHandler */
        foreach ($this->objects as $css) {

            switch ($css->getType()) {
                case 'file':

                    $filename = $css->getContent();

                    if (strpos($filename, BASEURL) !== false) {
                        $local_files[] = str_replace(BASEURL, BASEDIR, $filename);
                    }
                    else {
                        $files[] = $filename;
                    }

                    break;

                case 'inline':
                    $inline[] = $css->getContent();
                    break;
            }
        }

        $combined = '';

        // Any local files?
        if ($local_files || $inline) {

            if (!empty($local_files)) {
                foreach ($local_files as $css_file) {
                    $combined .= file_get_contents($css_file);
                }
            }

            if (!empty($inline)) {
                $combined .= implode(PHP_EOL, $inline);
            }

            $theme = 'Core';

            // Rewrite fonts paths
            $combined = str_replace('../fonts/', '../Themes/' . $theme . '/fonts/', $combined);

            // Rewrite images path
            $combined = str_replace('../img/', '../Themes/' . $theme . '/img/', $combined);
        }

        foreach ($this->processors as $processor) {
            $combined = $processor->process($combined);
        }

        return $combined;
    }
}
