<?php
namespace Core\Lib\Content;

use Core\Lib\Cfg;
use Core\Lib\Content\Html\HtmlFactory;
use Core\Lib\Errors\Exceptions\TemplateException;
use Core\Lib\Cache\Cache;

/**
 * Template.php
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
     * @throws TemplateException
     */
    final public function render()
    {
        foreach ($this->layers as $layer) {
            if (! method_exists($this, $layer)) {
                Throw new TemplateException('Template Error: The requested layer "' . $layer . '" does not exist.');
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
    final protected function getCss()
    {
        $files = $this->content->css->getFiles();

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
     *
     * @return array
     */
    final protected function getScript($area)
    {

        $files = $this->content->js->getFiles($area);

        // Init output var
        $html = '';

        if (empty($files)) {
            return $html;
        }

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
