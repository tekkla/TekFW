<?php
namespace Core\Lib\Content;

use Core\Lib\Cfg;
use Core\Lib\Cache\Cache;

/**
 * Css.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
final class Css
{

    /**
     * Storage of core css objects
     *
     * @var array
     */
    private static $core_css = [];

    /**
     * Storage of app css objects
     *
     * @var array
     */
    private static $app_css = [];

    /**
     * Type of css object
     *
     * @var string
     */
    private $type;

    /**
     * Css object content
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
     * @var Cache
     */
    private $cache;

    private $mode = 'apps';

    /**
     * Constructor
     *
     * @param Cfg $cfg
     */
    public function __construct(Cfg $cfg, Cache $cache)
    {
        $this->cfg = $cfg;
        $this->cache = $cache;
    }

    /**
     * Initiates core css
     */
    public function init()
    {
        $this->mode = 'core';

        // Bootstrap version from config
        $version = $this->cfg->get('Core', 'bootstrap_version');

        // Core and theme file
        $file = '/' . $this->cfg->get('Core', 'theme') . '/css/bootstrap.min.css';

        // Add existing local user/theme related bootstrap file or load it from cdn
        if ($this->cfg->get('Core', 'bootstrap_use_local') && file_exists(THEMESDIR . $file)) {
            $this->link(THEMESURL . $file);
        } else {
            // Add bootstrap main css file from cdn
            $this->link('https://maxcdn.bootstrapcdn.com/bootstrap/' . $this->cfg->get('Core', 'bootstrap_version') . '/css/bootstrap.min.css');
        }

        // Fontawesome file
        $file = '/' . $this->cfg->get('Core', 'theme') . '/css/font-awesome.min.css';

        // Add existing font-awesome font icon css file or load it from cdn
        if ($this->cfg->get('Core', 'fontawesome_use_local') && file_exists(THEMESDIR . $file)) {
            $this->link(THEMESURL . $file);
        } else {
            $this->link('https://maxcdn.bootstrapcdn.com/font-awesome/' . $this->cfg->get('Core', 'fontawesome_version') . '/css/font-awesome.min.css');
        }

        // Add general TekFW css file
        $file = '/' . $this->cfg->get('Core', 'theme') . '/css/Core.css';

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
            self::$core_css[] = $css;
        } else {
            self::$app_css[] = $css;
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
        $css = new CssObject();

        $css->setType('file');
        $css->setCss($url);

        return $this->add($css);
    }

    /**
     * Creates and returns an inline css object
     *
     * @param string $styles
     *
     * @return \Core\Lib\Css
     */
    public function &inline($styles)
    {
        $css = new CssObject();

        $css->setType('inline');
        $css->setCss($styles);

        return $this->add($css);
    }

    /**
     * Returns the current stack off css commands
     */
    public function getObjectStack()
    {
        return array_merge(self::$core_css, self::$app_css);
    }

    /**
     * Returns
     */
    public function getFiles()
    {
        $css_stack = $this->getObjectStack();

        $files = [];
        $local_files = [];
        $inline = [];

        /* @var $css Css */
        foreach ($css_stack as $css) {

            switch ($css->getType()) {
                case 'file':

                    $filename = $css->getCss();

                    if (strpos($filename, BASEURL) !== false) {
                        $local_files[] = str_replace(BASEURL, BASEDIR, $filename);
                    } else {
                        $files[] = $filename;
                    }

                    break;

                case 'inline':
                    $inline[] = $css->getCss();
                    break;
            }
        }

        $combined = '';

        // Any local files?
        if ($local_files) {

            // Yes! Now check cache
            $cache_object = $this->cache->createCacheObject();

            $key = 'combined';
            $extension = 'css';

            $cache_object->setKey($key);
            $cache_object->setExtension($extension);
            $cache_object->setTTL($this->cfg->get('Core', 'cache_ttl_css'));

            if ($this->cache->checkExpired($cache_object)) {

                foreach ($local_files as $filename) {
                    $combined .= file_get_contents($filename);
                }

                if ($inline) {
                    $combined .= implode(PHP_EOL, $inline);
                }

                // Minify combined css code
                $cssmin = new \CSSmin();
                $combined = $cssmin->run($combined);

                $theme = $this->cfg->get('Core', 'theme');

                // Rewrite fonts paths
                $combined = str_replace('../fonts/', '../Themes/' . $theme . '/fonts/', $combined);

                // Rewrite images path
                $combined = str_replace('../img/', '../Themes/' . $theme . '/img/', $combined);

                $cache_object->setContent($combined);

                $this->cache->put($cache_object);
            }

            $files[] = $this->cfg->get('Core', 'url_cache') . '/' . $key . '.' . $extension;
        }

        return $files;
    }
}
