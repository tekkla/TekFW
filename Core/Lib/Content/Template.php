<?php
namespace Core\Lib\Content;

use Core\Lib\Cfg;
use Core\Lib\IO\Cache;
use Core\Lib\Content\Html\HtmlFactory;

/**
 * Template parent class
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Template
{

    /**
     * Layers to render
     *
     * @var array
     */
    protected $layers = [
        'Head',
        'Body'
    ];

    /**
     *
     * @var Cfg
     */
    protected $cfg;

    /**
     *
     * @var Content
     */
    protected $content;

    /**
     *
     * @var HtmlFactory
     */
    protected $html;

    /**
     *
     * @var Cache
     */
    private $cache;

    /**
     * Constructor
     *
     * @param Cfg $cfg
     * @param Content $content
     * @param HtmlFactory $html
     */
    public function __construct(Cfg $cfg, Content $content, HtmlFactory $html, Cache $cache)
    {
        $this->cfg = $cfg;
        $this->content = $content;
        $this->html = $html;
        $this->cache = $cache;
    }

    /**
     * Renders the template
     *
     * Uses the $layer property to look for layers to be rendered. Will throw a
     * runtime exception when a requested layer does not exist in the called
     * template file.
     *
     * @throws \RuntimeException
     */
    final public function render()
    {
        foreach ($this->layers as $layer) {
            if (! method_exists($this, $layer)) {
                Throw new \RuntimeException('Template Error: The requested layer "' . $layer . '" does not exist.');
            }

            $this->$layer();
        }
    }

    /**
     * Creates and returns meta tags
     *
     * Set $data_only argument to true if you want to get get only the data
     * without a genereated html control.
     *
     * @param boolean $data_only
     *
     * @return string
     */
    final protected function getMeta($data_only = false)
    {
        $meta_stack = $this->content->meta->getTags();

        if ($data_only) {
            return $meta_stack;
        }

        ob_start();

        foreach ($meta_stack as $tag) {

            // $meta = $this->html->create('Elements\Meta');

            echo PHP_EOL . '<meta';

            foreach ($tag as $attribute => $value) {
                echo ' ', $attribute, '="', $value, '"';
            }

            echo '>';
        }

        $html = ob_get_contents();
    }

    /**
     * Creates and returns the title tag
     *
     * Set $data_only argument to true if you want to get get only the data
     * without a genereated html control.
     *
     * @param boolean $data_only
     *
     * @return string
     */
    final protected function getTitle($data_only = false)
    {
        if ($data_only) {
            return $this->content->getTitle();
        }

        return PHP_EOL . '<title>' . $this->content->getTitle() . '</title>';
    }

    /**
     * Returns html navbar or only the menu structure.
     *
     * Set $data_only argument to true if you want to get get only the data
     * without a genereated html control.
     *
     * @param boolean $data_only
     *
     * @return string|array
     */
    final protected function getMenu($name = '')
    {
        return $this->content->menu->getItems($name);
    }

    /**
     * Creates and return OpenGraph tags
     *
     * Set $data_only argument to true if you want to get get only the data
     * without a genereated html control.
     *
     * @param boolean $data_only
     *
     * @return string
     */
    final protected function getOpenGraph($data_only = false)
    {
        $og_stack = $this->content->og->getTags();

        if ($data_only) {
            return $og_stack;
        }

        ob_start();

        foreach ($og_stack as $property => $content) {
            echo '<meta property="', $property, '" content="', $content, '">', PHP_EOL;
        }

        $html = ob_get_contents();

        ob_end_clean();

        return $html;
    }

    /**
     * Creates and returns all css realted content
     *
     * Set $data_only argument to true if you want to get get only the data
     * without a genereated html control.
     *
     * @param boolean $data_only
     *
     * @return array|string
     */
    final protected function getCss($data_only = false)
    {
        $css_stack = $this->content->css->getObjectStack();

        if ($data_only) {
            return $css_stack;
        }

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
                    }
                    else {
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

                $cache_object->setContent($combined);

                $this->cache->put($cache_object);
            }

            $files[] = $this->cfg->get('Core', 'url_cache') . '/' . $key . '.' . $extension;
        }

        $html = '';

        // Start reading
        foreach ($files as $file) {
            $html .= PHP_EOL . '<link rel="stylesheet" type="text/css" href="' . $file . '">';
        }

        return $html;
    }

    /**
     * Creates and returns js script stuff for the requested area.
     *
     * Set $data_only argument to true if you want to get get only the data
     * without a genereated html control.
     *
     * @param string $area Valid areas are 'top' and 'below'.
     * @param boolean $data_only
     *
     * @return array|string
     */
    final protected function getScript($area, $data_only = false)
    {
        // Get scripts of this area
        $script_stack = $this->content->js->getScriptObjects($area);

        if (empty($script_stack)) {
            return false;
        }

        if ($data_only) {
            return $script_stack;
        }

        // Init js storages
        $files = $blocks = $inline = $scripts = $ready = $vars = [];

        // Include JSMin lib
        // if ($this->cfg->get('Core', 'js_minify')) {
        // require_once ($this->cfg->get('Core', 'dir_tools') . '/min/lib/JSMin.php');
        // }

        /* @var $script Javascript */
        foreach ($script_stack as $key => $script) {

            switch ($script->getType()) {

                // File to lin
                case 'file':
                    $filename = $script->getScript();

                    if (strpos($filename, BASEURL) !== false) {
                        $local_files[] = str_replace(BASEURL, BASEDIR, $filename);
                    }
                    else {
                        $files[] = $filename;
                    }
                    break;

                // Script to create
                case 'script':
                    $inline[] = $script->getScript();
                    break;

                // Dedicated block to embaed
                case 'block':
                    $blocks[] = $script->getScript();
                    break;

                // A variable to publish to global space
                case 'var':
                    $var = $script->getScript();
                    $vars[$var[0]] = $var[1];
                    break;

                // Script to add to $.ready()
                case 'ready':
                    $ready[] = $script->getScript();
                    break;
            }

            // Remove worked script object
            unset($script_stack[$key]);
        }

        $combined = '';

        // Check cache
        if ($local_files) {

            // Yes! Now check cache
            $cache_object = $this->cache->createCacheObject();

            $key = 'combined_' . $area;
            $extension = 'js';

            $cache_object->setKey($key);
            $cache_object->setExtension($extension);
            $cache_object->setTTL($this->cfg->get('Core', 'cache_ttl_js'));

            if ($this->cache->checkExpired($cache_object)) {

                // Create combined output
                foreach ($local_files as $filename) {
                    $combined .= file_get_contents($filename);
                }

                if ($inline) {
                    $combined .= implode(PHP_EOL, $inline);
                }

                if ($vars || $scripts || $ready) {

                    // Create script html object
                    foreach ($vars as $name => $val) {
                        $combined .= PHP_EOL . 'var ' . $name . ' = ' . (is_string($val) ? '"' . $val . '"' : $val) . ';';
                    }

                    // Create $(document).ready()
                    if ($ready) {
                        $combined .= PHP_EOL . '$(document).ready(function() {' . PHP_EOL;
                        $combined .= implode(PHP_EOL, $ready);
                        $combined .= PHP_EOL . '});';
                    }

                    // Add complete blocks
                    $combined .= implode(PHP_EOL, $blocks);
                }

                // Minify combined css code
                $combined = \JSMin::minify($combined);

                $cache_object->setContent($combined);

                $this->cache->put($cache_object);
            }
        }

        $files[] = $this->cfg->get('Core', 'url_cache') . '/' . $key . '.' . $extension;

        // Init output var
        $html = '';

        // Create files
        foreach ($files as $file) {

            // Create script html object
            $html .= PHP_EOL . '<script src="' . $file . '"></script>';
        }

        return $html;
    }

    /**
     * Create and returns head link elements
     *
     * Set $data_only argument to true if you want to get get only the data
     * without a genereated html control.
     *
     * @param boolean $data_only
     *
     * @return array|string
     */
    final protected function getHeadLinks($data_only = false)
    {
        $link_stack = $this->content->link->getLinkStack();

        if ($data_only) {
            return $link_stack;
        }

        ob_start();

        foreach ($link_stack as $link) {

            echo PHP_EOL . '<link';

            foreach ($link as $attribute => $value) {
                echo ' ', $attribute, '="', $value, '"';
            }

            echo '>';
        }

        $html = ob_get_contents();

        ob_end_clean();

        return $html;
    }

    /**
     * Creates and returns stored messages
     *
     * Set $data_only argument to true if you want to get get only the data
     * without a genereated html control.
     *
     * @param boolean $data_only
     *
     * @return array|string
     */
    final protected function getMessages($data_only = false)
    {
        $messages = $this->content->msg->getMessages();

        if ($data_only) {
            return $messages;
        }

        ob_start();

        echo '<div class="core-message">';

        foreach ($messages as $msg) {

            echo PHP_EOL, '
            <div class="alert alert-', $msg->getType(), $msg->getDismissable() ? ' alert-dismissable' : '';

            // Fadeout message?
            if ($this->cfg->get('Core', 'js_fadeout_time') > 0 && $msg->getFadeout()) {
                echo ' fadeout';
            }

            echo '">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                ', $msg->getMessage(), '
            </div>';
        }

        echo '</div>';

        $html = ob_get_contents();

        ob_end_clean();

        return $html;
    }

    /**
     * Creates breadcrumb html content or returns it's data-
     *
     * Set $data_only argument to true if you want to get get only the data
     * without a genereated html control.
     *
     * @param boolean $data_only
     *
     * @return array|string
     */
    final protected function getBreadcrumbs($data_only = false)
    {
        $breadcrumbs = $this->content->breadcrumbs->getBreadcrumbs();

        if ($data_only) {
            return $breadcrumbs;
        }

        // Add home button
        $text = $this->content->txt('home');

        if ($breadcrumbs) {
            $home_crumb = $this->content->breadcrumbs->createItem($text, BASEURL, $text);
        }
        else {
            $home_crumb = $this->content->breadcrumbs->createActiveItem($text, $text);
        }

        array_unshift($breadcrumbs, $home_crumb);

        ob_start();

        if ($breadcrumbs) {

            echo '<ol class="breadcrumb">';

            foreach ($breadcrumbs as $breadcrumb) {

                echo '<li';

                if ($breadcrumb->getActive()) {
                    echo ' class="active">' . $breadcrumb->getText();
                }
                else {
                    echo '><a href="' . $breadcrumb->getHref() . '">' . $breadcrumb->getText() . '</a>';
                }

                echo '</li>';
            }

            echo '</ol>';
        }

        $html = ob_get_contents();

        ob_end_clean();

        return $html;
    }

    /**
     * Returns default "core-scrolltotop" div html.
     *
     * @return string
     */
    protected function getScrollToTop()
    {
        return '<div id="core-scrolltotop"></div>';
    }

    /**
     * Returns default "core-modal" div html.
     *
     * @return string
     */
    protected function getModal()
    {
        return '<div id="core-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"></div>';
    }

    /**
     * Returns default "core-tooltip" div html.
     *
     * @return string
     */
    protected function getTooltip()
    {
        return '<div id="core-tooltip"></div>';
    }

    /**
     * Returns default "core-tooltip", "core-modal" and "core-scrolltotop" divs html.
     *
     * @return string
     */
    protected function getDisplayEssentials()
    {
        return $this->getTooltip() . $this->getModal() . $this->getScrollToTop();
    }

    /**
     * Returns the content generated by app call
     */
    final protected function getContent()
    {
        return $this->content->getContent();
    }
}
