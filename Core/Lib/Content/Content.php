<?php
namespace Core\Lib\Content;

use Core\Lib\Cfg;
use Core\Lib\Http\Router;
use Core\Lib\Content\Html\HtmlFactory;
use Core\Lib\Amvc\Creator;

/**
 * Content
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @licencse MIT
 * @copyright 2014
 */
class Content
{

    use \Core\Lib\Traits\TextTrait;

    /**
     *
     * @var Cfg
     */
    private $cfg;

    /**
     *
     * @var Router
     */
    private $router;

    /**
     *
     * @var Creator
     */
    private $app_creator;

    /**
     *
     * @var string
     */
    private $title;

    /**
     *
     * @var Meta
     */
    public $meta;

    /**
     *
     * @var Css
     */
    public $css;

    /**
     *
     * @var OpenGraph
     */
    public $og;

    /**
     *
     * @var Link
     */
    public $link;

    /**
     *
     * @var Javascript
     */
    public $js;

    /**
     *
     * @var Menu
     */
    public $navbar;

    /**
     *
     * @var Breadcrumb
     */
    public $breadcrumbs;

    /**
     *
     * @var string
     */
    private $content;

    /**
     *
     * @var HtmlFactory
     */
    private $html;

    /**
     *
     * @var Message
     */
    public $msg;

    private $debug = [];

    /**
     * Constructor
     *
     * @param Router $router
     * @param Cfg $cfg
     * @param Creator $app_creator
     * @param HtmlFactory $html
     * @param Menu $menu
     * @param Css $css
     * @param Javascript $js
     * @param Message $msg
     */
    public function __construct(Router $router, Cfg $cfg, Creator $app_creator, HtmlFactory $html, Menu $menu, Css $css, Javascript $js, Message $msg)
    {
        $this->router = $router;
        $this->cfg = $cfg;
        $this->app_creator = $app_creator;
        $this->html = $html;
        $this->navbar = $menu;
        $this->js = $js;
        $this->css = $css;
        $this->msg = $msg;

        $this->meta = new Meta();
        $this->og = new OpenGraph();
        $this->link = new Link();

        $this->breadcrumbs = new Breadcrumb();

        // Try to init possible content handler
        if ($this->cfg->exists('Core', 'content_handler') && $this->router->isAjax()) {

            // Get instance of content handler app
            $app = $this->app_creator->create($this->cfg->get('Core', 'content_handler'));

            // Init method to call exists?
            if (method_exists($app, 'initContentHandler')) {
                $app->initContentHandler();
            }
        }
    }

    /**
     * Set pagetitle
     *
     * @param string $title
     *
     * @return \Core\Lib\Content\Content
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Set content to show
     *
     * @param string $content
     *
     * @return \Core\Lib\Content\Content
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Checks for a set config handler in web config
     *
     * @return boolean
     */
    public function hasContentHandler()
    {
        return $this->cfg->exists('Core', 'content_handler');
    }

    /**
     * Returns the name of config handler set in web config
     *
     * @return string
     */
    public function getContenHandler()
    {
        return $this->cfg->get('Core', 'content_handler');
    }

    /**
     * Handles, enhances and returns the content of the page
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function getContent()
    {
        $this->content = '<div id="message"></div>' . $this->content;

        // Fill in content
        try {

            // Try to run set content handler on non ajax request
            if ($this->cfg->exists('Core', 'content_handler') && ! $this->router->isAjax()) {

                // We need the name of the ContentCover app
                $app_name = $this->cfg->get('Core', 'content_handler');

                // Get instance of this app
                $app = $this->appcreator->create($app_name);

                // Check for existing ContenCover method
                if (! method_exists($app, 'runContentHandler')) {
                    Throw new \RuntimeException('You set the app "' . $app_name . '" as content handler but it lacks of method "runContentHandler()". Correct either the config or add the needed method to this app.');
                }

                // Everything is all right. Run content handler by giving the current content to it.
                $this->content .= $app->runContentHandler($this->content);
            }
        } catch (\Exception $e) {

            // Add error message above content
            $this->content .= '<div class="alert alert-danger alert-dismissable">' . $e->getMessage() . '</div>';
        }

        // Add framework status elements
        $this->content .= '
		<div id="status"><i class="fa fa-spinner fa-spin"></i></div>
		<div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"></div>
		<div id="debug"></div>
		<div id="tooltip"></div>
		<div id="scrolltotop"></div>';

        // # Insert content
        return $this->content;
    }

    /**
     * Renders and echoes template
     *
     * @param string $template
     *            Name of template
     * @param string $theme
     *            Name of theme template is from
     */
    public function render($template = 'Index', $theme = 'Core')
    {
        $class = '\Themes\\' . $theme . '\\' . $template . 'Template';
        $template = new $class($this->cfg, $this, $this->html);

        echo $template->render();
    }

    /**
     * Returns set pagetitle
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns sitename
     *
     * @return \Core\Lib\mixed
     */
    public function getBrand()
    {
        return $this->cfg->get('Core', 'sitename');
    }

    /**
     * Adds data to debug output
     *
     * @param string $debug_data
     *
     * @return \Core\Lib\Content\Content
     */
    public function addDebug($debug_data)
    {
        $this->debug[] = $debug_data;

        return $this;
    }

    public function getDebug(){
        return $this->debug;
    }
}


// Experimental SEO url converter...
// if (Cfg::get('Core', 'url_seo'))
// {
	// $match_it = function($match) {
	// return Url::convertSEF($match);
		// };

		// $html = preg_replace_callback('@(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()]+|\(([^\s()]+|(\([^\s()]+\)))*\))+(?:\(([^\s()]+|(\([^\s()]+\)))*\)|[^\s`!()\[\]{};:\'".,?«»“”‘’]))@', $match_it($match), $html);
		// }
		// echo $html;

