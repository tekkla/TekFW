<?php
namespace Core\Page\Head\Css;

use Core\Cfg\Cfg;

/**
 * Css.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
final class Css
{

    /**
     * Storage of core css objects
     *
     * @var array
     */
    private $core_css = [];

    /**
     * Storage of app css objects
     *
     * @var array
     */
    private $app_css = [];

    /**
     * Type of css object
     *
     * @var string
     */
    private $type;

    /**
     *
     * @var string
     */
    private $content;

    /**
     *
     * @var Cfg
     */
    private $cfg;

    /**
     *
     * @var string
     */
    private $mode = 'apps';

    /**
     * Constructor
     *
     * @param Cfg $cfg
     */
    public function __construct(Cfg $cfg)
    {
        $this->cfg = $cfg;
    }

    /**
     * Initiates core css
     */
    public function init()
    {
        $this->mode = 'core';

        // Theme name
        $theme = $this->cfg->data['Core']['style.theme.name'];

        // Bootstrap version from config
        $version = $this->cfg->data['Core']['style.bootstrap.version'];

        // Core and theme file
        $file = '/' . $theme . '/css/bootstrap-' . $version . '.css';

        // Add existing local user/theme related bootstrap file or load it from cdn
        if ($this->cfg->data['Core']['style.bootstrap.local'] && file_exists(THEMESDIR . $file)) {
            $this->link(THEMESURL . $file);
        }
        else {
            // Add bootstrap main css file from cdn
            $this->link('https://maxcdn.bootstrapcdn.com/bootstrap/' . $version . '/css/bootstrap.min.css');
        }

        // Fontawesome version
        $version = $this->cfg->data['Core']['style.fontawesome.version'];

        // Fontawesome file
        $file = '/' . $theme . '/css/font-awesome-' . $version . '.css';

        // Add existing font-awesome font icon css file or load it from cdn
        if ($this->cfg->data['Core']['style.fontawesome.local'] && file_exists(THEMESDIR . $file)) {
            $this->link(THEMESURL . $file);
        }
        else {
            $this->link('https://maxcdn.bootstrapcdn.com/font-awesome/' . $version . '/css/font-awesome.min.css');
        }

        // Add general TekFW css file
        $file = '/' . $theme . '/css/Core.css';

        $this->link(THEMESURL . $file);

        $this->mode = 'apps';
    }

    /**
     * Adds a css object to the output queue
     *
     * @param Css $css
     */
    public function &add(CssObject $css)
    {
        if ($this->mode == 'core') {
            $this->core_css[] = $css;
        }
        else {
            $this->app_css[] = $css;
        }

        return $css;
    }

    /**
     * Creates and returns a link css object.
     *
     * @param string $url
     *
     * @return Css
     */
    public function &link($url)
    {
        $css_object = new CssObject();

        $css_object->setType('file');
        $css_object->setCss($url);

        $this->add($css_object);

        return $css_object;
    }

    /**
     * Creates and returns an inline css object
     *
     * @param string $styles
     *
     * @return \Core\Css
     */
    public function &inline($styles)
    {
        $css_object = new CssObject();

        $css_object->setType('inline');
        $css_object->setCss($styles);

        $this->add($css_object);

        return $css_object;
    }

    /**
     * Returns the current stack off css commands
     */
    public function getObjectStack()
    {
        return array_merge($this->core_css, $this->app_css);
    }

    /**
     * Returns
     */
    public function getFiles()
    {
        $css_stack = $this->getObjectStack();

        if (empty($css_stack)) {
            return $css_stack;
        }

        $files = [];
        $local_files = [];
        $inline = [];

        /* @var $css Css */
        foreach ($css_stack as $css_object) {

            switch ($css_object->getType()) {
                case 'file':

                    $filename = $css_object->getCss();

                    if (strpos($filename, BASEURL) !== false) {
                        $local_files[] = str_replace(BASEURL, BASEDIR, $filename);
                    }
                    else {
                        $files[] = $filename;
                    }

                    break;

                case 'inline':
                    $inline[] = $css_object->getCss();
                    break;
            }
        }

        $combined = '';

        // Any local files?
        if ($local_files || $inline) {

            $key = 'combined';
            $extension = 'css';

            $filename = $this->cfg->data['Core']['dir.cache'] . '/' . $key . '.' . $extension;

            // End of combined file TTL reached?
            if (!file_exists($filename) || filemtime($filename) + $this->cfg->data['Core']['cache.ttl.' . $extension] < time()) {

                if (! empty($local_files)) {
                    foreach ($local_files as $css_file) {
                        $combined .= file_get_contents($css_file);
                    }
                }

                if (! empty($inline)) {
                    $combined .= implode(PHP_EOL, $inline);
                }

                // Minify combined css code
                $css_min = new \CSSmin();
                $combined = $css_min->run($combined);

                $theme = $this->cfg->data['Core']['style.theme.name'];

                // Rewrite fonts paths
                $combined = str_replace('../fonts/', '../Themes/' . $theme . '/fonts/', $combined);

                // Rewrite images path
                $combined = str_replace('../img/', '../Themes/' . $theme . '/img/', $combined);

                file_put_contents($filename, $combined);
            }

            $files[] = $this->cfg->data['Core']['url.cache'] . '/' . $key . '.' . $extension;
        }

        return $files;
    }
}
