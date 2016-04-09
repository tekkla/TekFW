<?php
namespace Core\Lib\Page;

use Core\Lib\Cfg\Cfg;
use Core\Lib\Router\Router;
use Core\Lib\Html\HtmlFactory;
use Core\Lib\Amvc\Creator;
use Core\Lib\Language\TextTrait;
use Core\Lib\Amvc\Controller;

// Page Head Libs
use Core\Lib\Page\Head\Meta;
use Core\Lib\Page\Head\OpenGraph;
use Core\Lib\Page\Head\Link;
use Core\Lib\Page\Head\Css\Css;

// Page Body Libs
use Core\Lib\Page\Head\Javascript\Javascript;
use Core\Lib\Page\Body\Menu\Menu;
use Core\Lib\Page\Body\Message\Message;

/**
 * Page.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Page
{

    use TextTrait;

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
    public $menu;

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
     * Message Sservice
     *
     * @var Message
     */
    public $message;

    private $debug = [];

    /**
     *
     * @var Controller
     */
    private $controller;

    /**
     *
     * @var string
     */
    private $action;

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
     * @param Message $message
     */
    public function __construct(Router $router, Cfg $cfg, Creator $app_creator, HtmlFactory $html, Menu $menu, Css $css, Javascript $js, Message $message)
    {
        $this->router = $router;
        $this->cfg = $cfg;
        $this->app_creator = $app_creator;
        $this->html = $html;
        $this->menu = $menu;
        $this->js = $js;
        $this->css = $css;
        $this->message = $message;

        $this->meta = new Meta();
        $this->og = new OpenGraph();
        $this->link = new Link();

        $this->breadcrumbs = $this->html->create('Bootstrap\Breadcrumb\Breadcrumb');
    }

    public function init()
    {
        static $done = false;

        if ($done) {
            return;
        }

        $this->css->init();
        $this->js->init();

        $done = true;
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
        return $this->cfg->exists('Core', 'execute.content_handler');
    }

    /**
     * Returns the name of config handler set in web config
     *
     * @return string
     */
    public function getContenHandler()
    {
        return $this->cfg->get('Core', 'execute.content_handler');
    }

    /**
     * Handles, enhances and returns the content of the page
     *
     * @throws ConfigException
     *
     * @return string
     */
    public function getContent()
    {

        // ContentHandler defined?
        try {

            // Try to run set content handler on non ajax request
            if ($this->cfg->exists('Core', 'execute.content_handler')) {

                // We need the name of the ContentCover app
                $app_name = $this->cfg->get('Core', 'execute.content_handler');

                // Get instance of this app
                $app = $this->app_creator->getAppInstance($app_name);

                // Check for existing ContenCover method
                if (! method_exists($app, 'ContentHandler')) {
                    Throw new PageException('You set the app "' . $app_name . '" as content handler but it lacks of method "ContentHandler()". Correct either the config or add the needed method to the apps mainfile (' . $app_name . '.php).');
                }

                // Everything is all right. Run content handler by giving the current content to it.
                $this->content = $app->ContentHandler($this->content);
            }
        }
        catch (\Exception $e) {

            // Get error info
            $error = $this->di->get('core.error')->handleException($e);

            // Add error message above content
            $this->msg->danger($error);
        }

        // # Insert content
        return $this->content;
    }

    /**
     * Renders template
     *
     * @param string $template
     *            Name of template
     * @param string $theme
     *            Name of theme template is from
     */
    public function render($template = 'Index', $theme = 'Core')
    {
        // Add missing title
        if (empty($this->title)) {
            $this->title = $this->cfg->get('Core', 'site.general.name');
        }

        if ($theme == 'Core' && $this->cfg->exists('Core', 'style.theme')) {
            $theme = $this->cfg->get('Core', 'style.theme');
        }

        $class = '\Themes\\' . $theme . '\\' . $template . 'Template';

        $template = new $class($this->cfg, $this, $this->html);

        return $template->render();
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
     * Returns base url.
     *
     * @return string
     */
    public function getHomeUrl()
    {
        return BASEURL;
    }

    /**
     * Returns sitename
     *
     * @return \Core\Lib\mixed
     */
    public function getBrand()
    {
        return $this->cfg->get('Core', 'site.general.name');
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

    /**
     * Returns stored debug data.
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * Sets one or more headers which replaces already set headers.
     *
     * @param string|array $headers
     *
     * @return \Core\Lib\Content\Content
     */
    public function setHeader($headers = [])
    {
        if (! is_array($headers)) {
            $headers = (array) $headers;
        }

        $this->headers = $headers;

        return $this;
    }

    /**
     * Adds one or more headers to already set headers.
     *
     * @param string|array $headers
     *
     * @return \Core\Lib\Content\Content
     */
    public function addHeader($headers = [])
    {
        if (! is_array($headers)) {
            $headers = (array) $headers;
        }

        foreach ($headers as $header) {
            $this->headers[] = $headers;
        }

        return $this;
    }
}
