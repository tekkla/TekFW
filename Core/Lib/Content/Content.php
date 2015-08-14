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

    public function create()
    {

        // Match request against stored routes
        $this->router->match();

        // --------------------------------------
        // 8. Run called app
        // --------------------------------------

        // Try to use appname provided by router
        $app_name = $this->router->getApp();

        // No app by request? Try to get default app from config or set Core as
        // default app
        if (! $app_name) {
            $app_name = $this->cfg->exists('Core', 'default_app') ? $this->cfg->get('Core', 'default_app') : 'Core';
        }

        // Start with factoring the requested app

        /* @var $app \Core\Lib\Amvc\App */
        $app = $this->app_creator->create($app_name);

        /**
         * Each app can have it's own start procedure.
         * This procedure is used to
         * init apps with more than the app creator does. To use this feature the
         * app needs run() method in it's main file.
         */
        if (method_exists($app, 'run')) {
            $app->run();
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
        if ($this->router->isAjax()) {
            $this->createAjax();
        }
        else {
            $this->createFull();
        }
    }

    private function createAjax()
    {
        // Send cache preventing headers and set content type
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

        switch ($this->router->getFormat()) {

            case 'json':
                $this->createJson();
                break;

            case 'xml':
                $this->createXml();
                break;

            case 'html':
            default:
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

                // End end here
                exit();

                break;
        }
    }

    private function createFull()
    {
        switch ($this->router->getFormat()) {

            case 'json':
                $this->createJson();
                break;

            case 'xml':
                $this->createXml();
                break;

            case 'html':
            default:

                // Always use UTF-8
                header("Content-Type: text/html; charset=utf-8");

                // Add missing title
                if (empty($this->title)) {
                    $this->title = $this->cfg->get('Core', 'sitename');
                }

                Try {

                    // Run controller and store result
                    $result = $this->controller->run($this->action, $this->router->getParam());

                    // No content created? Check app for onEmpty() event which maybe gives us content.
                    if (empty($result) && method_exists($app, 'onEmpty')) {
                        $result = $app->onEmpty();
                    }

                    // Append content provided by apps onBefore() event method
                    if (method_exists($app, 'onBefore')) {
                        $result = $app->onBefore() . $result;
                    }

                    // Prepend content provided by apps onAfter() event method
                    if (method_exists($app, 'onAfter')) {
                        $result .= $app->onAfter();
                    }
                }
                catch (\Exception $e) {

                    $result = $this->di->get('core.error')->handleException($e);
                }

                $this->setContent($result);

                // Call content builder
                echo $this->render();
                break;
        }
    }

    private function createJson()
    {
        header('Content-type: application/json; charset=utf-8");');
        echo json_encode($this->controller->run($this->action, $this->router->getParam()));
        exit();
    }

    private function createXml()
    {
        header("Content-Type: application/xml; charset=utf-8");
        echo $this->controller->run($this->action, $this->router->getParam());
        exit();
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
        // Add messageblock
        $this->content = '<div id="message"></div>' . $this->content;

        // Fill in content
        try {

            // Try to run set content handler on non ajax request
            if ($this->cfg->exists('Core', 'content_handler') && ! $this->router->isAjax()) {

                // We need the name of the ContentCover app
                $app_name = $this->cfg->get('Core', 'content_handler');

                // Get instance of this app
                $app = $this->app_creator->create($app_name);

                // Check for existing ContenCover method
                if (! method_exists($app, 'runContentHandler')) {
                    Throw new \RuntimeException('You set the app "' . $app_name . '" as content handler but it lacks of method "runContentHandler()". Correct either the config or add the needed method to the apps mainfile (' . $app_name . '.php).');
                }

                // Everything is all right. Run content handler by giving the current content to it.
                $this->content .= $app->runContentHandler($this->content);
            }
        }
        catch (\Exception $e) {

            // Add error message above content
            $this->content .= '<div class="alert alert-danger">' . $this->di->get('core.error')->handleException($e) . '</div>';
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
     * @param string $template Name of template
     * @param string $theme Name of theme template is from
     */
    public function render($template = 'Index', $theme = 'Core')
    {
        if ($theme == 'Core' && $this->cfg->exists('Core', 'theme')) {
            $theme = $this->cfg->get('Core', 'theme');
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

    public function getDebug()
    {
        return $this->debug;
    }
}
