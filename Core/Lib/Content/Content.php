<?php
namespace Core\Lib\Content;

use Core\Lib\Cfg;
use Core\Lib\Http\Router;
use Core\Lib\Content\Html\HtmlFactory;
use Core\Lib\Amvc\Creator;
use Core\Lib\Traits\TextTrait;
use Core\Lib\Traits\DebugTrait;
use Core\Lib\Amvc\Controller;

/**
 * Content
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2015
 */
class Content
{

    use TextTrait;
    use DebugTrait;

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
     *
     * @var Message
     */
    public $msg;

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
     *
     * @var array
     */
    private $headers = [];


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
        $this->menu = $menu;
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
            $app = $this->app_creator->getAppInstance($this->cfg->get('Core', 'content_handler'));

            // Init method to call exists?
            if (method_exists($app, 'InitContentHandler')) {
                $app->InitContentHandler();
            }
        }
    }

    public function create()
    {
        // Match request against stored routes
        $this->router->match();

        // Try to use appname provided by router
        $app_name = $this->router->getApp();

        // No app by request? Try to get default app from config or set Core as
        // default app
        if (! $app_name) {
            $app_name = $this->cfg->exists('Core', 'default_app') ? $this->cfg->get('Core', 'default_app') : 'Core';
        }

        // Start with factoring the requested app

        /* @var $app \Core\Lib\Amvc\App */
        $app = $this->app_creator->getAppInstance($app_name);

        if (method_exists($app, 'Access')) {

            // Call app wide access method. This is important for using forceLogin() security method.
            $app->Access();

            // Check for redirect from Access() method!!!
            if ($app_name != $this->router->getApp()) {

                // Get new appname and create this app instead of the requested one.
                $app_name = $this->router->getApp();

                /* @var $app \Core\Lib\Amvc\App */
                $app = $this->app_creator->getAppInstance($app_name);
            }
        }

        /**
         * Each app can have it's own start procedure.
         * This procedure is used to init apps with more than the app creator does.
         * To use this feature the app needs a run() method in it's main file.
         */
        if (method_exists($app, 'Run')) {
            $app->Run();
        }

        // Get name of requested controller
        $controller_name = $this->router->getController();

        // Set controller name to "Index" when no controller name has been returned
        // from request handler
        if (empty($controller_name)) {
            $controller_name = $this->router->checkParam('ctrl') ? $this->router->getParam('ctrl') : 'Index';
        }

        // Load controller object
        $this->controller = $app->getController($controller_name);

        // Which controller action has to be run?
        $this->action = $this->router->getAction();

        // No action => use Index as default
        if (empty($this->action)) {
            $this->action = $this->router->checkParam('action') ? $this->router->getParam('action') : 'Index';
        }

        // Run controller and process result.
        return $this->router->isAjax() ? $this->createAjax() : $this->createFull();
    }

    private function createAjax()
    {
        // Send cache preventing headers and set content type
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

        try {
            // Result will be processed as ajax command list
            $this->controller->ajax($this->action, $this->router->getParam());
        }
        catch (\Exception $e) {
            $this->di->get('core.error')->handleException($e, false, true);
        }

        // send JSON header
        header('Content-type: application/json; charset=utf-8");');

        // Run ajax processor
        echo $this->di->get('core.ajax')->process();

        return false;
    }

    private function createFull()
    {
        Try {

            // Run controller and store result
            $result = $this->controller->run($this->action, $this->router->getParam());

            /*
             * // No content created? Check app for onEmpty() event which maybe gives us content.
             * if (empty($result) && method_exists($app, 'onEmpty')) {
             * $result = $app->onEmpty();
             * }
             *
             * // Append content provided by apps onBefore() event method
             * if (method_exists($app, 'onBefore')) {
             * $result = $app->onBefore() . $result;
             * }
             *
             * // Prepend content provided by apps onAfter() event method
             * if (method_exists($app, 'onAfter')) {
             * $result .= $app->onAfter();
             * }
             */
        }
        catch (\Exception $e) {

            $result = $this->di->get('core.error')->handleException($e);
        }

        switch ($this->router->getFormat()) {

            case 'json':
                $this->headers[] = 'Content-type: application/json; charset=utf-8");';
                $this->sendHeader();
                echo json_encode($result);
                return false;

            case 'xml':
                $this->headers[] = "Content-Type: application/xml; charset=utf-8";
                $this->sendHeader();
                echo $result;
                return false;

            case 'file':

                $this->sendHeader();
                readfile($result);
                return false;

            case 'html':
            default:

                $this->css->init();
                $this->js->init();

                // Always use UTF-8
                $this->headers[] ="Content-Type: text/html; charset=utf-8";

                // Add missing title
                if (empty($this->title)) {
                    $this->title = $this->cfg->get('Core', 'sitename');
                }

                $this->setContent($result);

                // Call content builder

                $this->sendHeader();

                echo $this->render();

                return true;
        }
    }

    /**
     * Sends all stored header data.
     *
     * @throws \RuntimeException
     *
     * @return Content
     */
    private function sendHeader()
    {
        if (headers_sent()) {
            Throw new \RuntimeException('Cannot sent headers. Headers are already sent somewhere. You have to use setHeader() method in controller.');
        }

        foreach ($this->headers as $header) {
            header($header);
        }

        return $this;
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

        // ContentHandler defined?
        try {

            // Try to run set content handler on non ajax request
            if ($this->cfg->exists('Core', 'content_handler')) {

                // We need the name of the ContentCover app
                $app_name = $this->cfg->get('Core', 'content_handler');

                // Get instance of this app
                $app = $this->app_creator->getAppInstance($app_name);

                // Check for existing ContenCover method
                if (! method_exists($app, 'ContentHandler')) {
                    Throw new \RuntimeException('You set the app "' . $app_name . '" as content handler but it lacks of method "ContentHandler()". Correct either the config or add the needed method to the apps mainfile (' . $app_name . '.php).');
                }

                // Everything is all right. Run content handler by giving the current content to it.
                $this->content = $app->ContentHandler($this->content);
            }
        }
        catch (\Exception $e) {

            // Get error info
            $error=  $this->di->get('core.error')->handleException($e);

            // Add error message above content
            $this->msg->danger($error);
        }


        // # Insert content
        return $this->content;
    }

    /**
     * Renders and echoes template
     *
     * @param string $template Name of template
     * @param string $theme Name of theme template is from
     */
    public function render($template = 'Index', $theme = 'Core')
    {
        if ($theme == 'Core' && $this->cfg->exists('Core', 'theme')) {
            $theme = $this->cfg->get('Core', 'theme');
        }

        $class = '\Themes\\' . $theme . '\\' . $template . 'Template';

        $template = new $class($this->cfg, $this, $this->html, $this->di->get('core.io.cache'));

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
    public function setHeader($headers=[])
    {
        if (!is_array($headers)) {
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
    public function addHeader($headers=[])
    {
        if (!is_array($headers)) {
            $headers = (array) $headers;
        }

        foreach ($headers as $header) {
            $this->headers[] = $headers;
        }

        return $this;
    }
}
