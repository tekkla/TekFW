<?php
namespace Core\Amvc;

use Core\Router\Router;
use Core\Http\Http;
use Core\Security\Security;
use Core\Page\Page;
use Core\Html\HtmlFactory;
use Core\Html\FormDesigner\FormDesigner;
use Core\Ajax\Ajax;
use Core\Ajax\AjaxCommandAbstract;
use Core\Router\UrlTrait;
use Core\Traits\ArrayTrait;
use Core\Cfg\CfgTrait;
use Core\Language\TextTrait;
use Core\IO\IO;

/**
 * Controller.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Controller extends MvcAbstract
{

    use UrlTrait;
    use TextTrait;
    use CfgTrait;
    use ArrayTrait;

    /**
     * Type of class
     *
     * @var String
     */
    protected $type = 'Controller';

    /**
     * Signals that the corresponding view will be rendered
     *
     * @var Boolean
     */
    protected $render = true;

    /**
     * Storage for access rights
     *
     * @var array
     */
    protected $access = [];

    /**
     *
     * @var string
     */
    private $action = 'Index';

    /**
     * Storage for parameter
     *
     * @var array
     */
    private $params = [];

    /**
     * Stores the controller bound Model object.
     * Is false when controller has no model.
     *
     * @var Model
     */
    public $model = false;

    /**
     *
     * @var View
     */
    private $view;

    /**
     * Security Service
     *
     * @var Security
     */
    protected $security;

    /**
     * Http Service Wrapper
     *
     * @var Http
     */
    protected $http;

    /**
     * Router Service
     *
     * @var Router
     */
    protected $router;

    /**
     * Page Service
     *
     * @var Page
     */
    protected $page;

    /**
     *
     * @var HtmlFactory
     */
    protected $html;

    /**
     * Ajax service
     *
     * @var Ajax
     */
    protected $ajax;

    /**
     * IO service
     *
     * @var IO
     */
    protected $io;

    /**
     * Flag to signal that this controller is in ajax mode
     *
     * @var bool
     */
    private $ajax_flag = false;

    /**
     *
     * @var AjaxCommandAbstract
     */
    private $ajax_command;

    /**
     * Constructor
     *
     * @param string $name
     *            The Name of our controller
     *
     * @param App $app
     *            App dependency
     * @param Router $router
     *            Router dependency
     * @param Http $http
     *            Http dependency
     * @param Security $security
     *            Security dependency
     * @param Page $page
     *            Page dependency
     * @param HtmlFactory $html
     *            HtmlFactory dependency
     * @param Ajax $ajax
     *            Ajax dependency
     * @param IO $io
     *            IO dependency
     */
    final public function __construct($name, App $app, Router $router, Http $http, Security $security, Page $page, HtmlFactory $html, Ajax $ajax, IO $io)
    {
        // Store name
        $this->name = $name;
        $this->app = $app;
        $this->router = $router;
        $this->http = $http;
        $this->security = $security;
        $this->page = $page;
        $this->html = $html;
        $this->ajax = $ajax;
        $this->io = $io;

        // Model to bind?
        $this->model = $this->app->getModel($name);

        // Controller of an ajax request?
        if ($this->router->isAjax()) {
            $this->ajax_flag = true;
            $this->ajax_command = $this->ajax->createCommand('Dom\Html');
        }
    }

    /**
     * Runs the requested controller action
     *
     * This is THE run method for each controller in each app. When used to resolve
     * requests, no action or parameter need to be set. Both will be autodiscovered
     * by requesting the needed data from the requesthandler. The method can also be
     * used to get a partial result when using a controller from within a controller.
     * In this case you should provide the action an parameters needed to get the
     * wanted result.
     *
     * @param string $action
     *            Action method to call
     * @param array $params
     *            Optional parametername => $value based array to be used as action method parameter
     *
     * @throws ControllerException
     *
     * @return boolean bool|string
     */
    final public function run($action, array $params = [])
    {
        // If accesscheck failed => stop here and return false!
        if ($this->checkControllerAccess() == false) {
            $this->page->message->warning($this->text('access.missing_userrights'));

            // @TODO implement logging
            // $this->page->logSuspicious('Missing permission for ressource ' . $this->app->getName() . '.' .
            // $this->getName() . '.' . $action . '()');
            return false;
        }

        // Use givem params
        if (empty($action)) {
            Throw new ControllerException(sprintf('The action name for %s::run() is empty.', $this->name));
        }

        $this->action = $action;

        // Use givem params
        if (! empty($params) && ! $this->arrayIsAssoc($params)) {
            Throw new ControllerException('Parameter arguments on Controller::run() methods need to be key based where the key represents the arguments name to be used for in action call.');
        }

        $this->params = $params;

        // Init return var with boolean false as default value. This default
        // prevents from running the views render() method when the controller
        // action is stopped manually by using return.
        $return = false;

        // a little bit of reflection magic to pass request param into controller func
        $return = $this->di->invokeMethod($this, $this->action, $this->params);

        // Do we have a result?
        if (isset($return)) {

            // Boolean false result means to stop work for controller
            if ($return == false) {
                return false;
            }

            // Control rendering by requested output format
            switch ($this->router->getFormat()) {

                // Turn rendering off on json and xml format
                case 'json':
                case 'xml':
                case 'file':
                    $this->render = false;
                    break;
            }
        }

        // Render the view and return the result
        if ($this->render === true) {

            // Create view instance if not alredy done
            if (! $this->view instanceof View) {
                $this->view = $this->app->getView($this->name);
            }

            // Render into own outputbuffer
            ob_start();

            // Render
            $this->view->render($this->action, $this->params);

            // Get content from buffer
            $content = ob_get_clean();

            // Run possible onEmpty event of app on no render result
            if (empty($content) && method_exists($this->app, 'onEmpty')) {
                $content = $this->app->onEmpty();
            }

            return $content;
        }
        else {

            // Without view rendering we return the return value send from called controller action
            return $return;
        }
    }

    /**
     * Ajax method to send the result of an action as ajax html command
     *
     * This works similiar to the run() method and even uses it. The difference is that the renderesult is wrapped by an
     * ajax command object. This ajax command can be controlled by setting the wanted parameters via $this->ajax->...
     *
     * @param string $action
     *            Name of the action to call
     * @param array $params
     *            Array of parameter to be used in action call
     * @param string $selector
     *            Optional jQuery selector to html() the result.
     *            Can be overridden by setAjaxTarget() method.
     *            Default: '#content'
     */
    final public function ajax($action = 'Index', $params = [], $selector = '#content')
    {
        $content = $this->run($action, $params);

        if ($content !== false) {

            $this->ajax_command->setArgs($content);
            $this->ajax_command->setId(get_called_class() . '::' . $action);

            if (! $this->ajax_command->getSelector() && $selector) {
                $this->ajax_command->setSelector($selector);
            }

            $this->ajax_command->send();
        }

        return $this;
    }

    /**
     * Redirects from one action to another
     *
     * @param string|array $target
     *            Name of redirectaction to call within this controller or an array aof app, controller and action name
     *            which will be called as redirect.
     * @param array $params
     *            Optional key => value array of params to pass into redirect action (Default: empty array)
     * @param bool $clear_post
     *            Optional flag to control emptying posted data (Default: true)
     *
     * @return mixed
     */
    final protected function redirect($target, $params = [], $clear_post = true)
    {

        // Clean data
        if ($clear_post) {
            $this->http->post->clean();
        }

        // Target is a string and is treated like an action name
        if (is_array($target)) {

            $require = [
                'app',
                'controller',
                'action'
            ];

            foreach ($require as $check) {
                if (empty($target[$check])) {
                    Throw new ControllerException(sprintf('Redirects by arrayed $target need a set "%s" element.', $check));
                }
            }

            $app = $this->app->creator->getAppInstance($target['app']);
            $controller = $app->getController($target['controller']);

            return $controller->run($target['action'], $params);
        }
        else {
            return $this->run($target, $params);
        }
    }

    /**
     * Method to cleanup data in controllers model and the request handler
     *
     * @param bool $model
     *            Flag to clean model data (default: true)
     * @param string $post
     *            Flag to clean post data (default: true)
     */
    final protected function cleanUp($post = true)
    {
        // Reset post data
        if ($post) {
            $this->http->post->clean();
        }
    }

    /**
     * Does an urlredirect but cares about what kind (ajax?) of request was send.
     *
     * @param Url|string $url
     */
    final protected function doRefresh($url)
    {
        if ($this->router->isAjax()) {
            $this->ajax->refresh($url);
        }
        else {
            $this->redirectExit($url);
        }
    }

    /**
     * Check userrights against one or mor permissions
     *
     * @param string|array $perm
     *            One permission (string) or an indexed arry of permissions to check
     *
     * @return boolean
     */
    final protected function checkUserrights($perm)
    {
        return $this->security->user->checkAccess($perm);
    }

    /**
     * Checks the controller access of the user
     *
     * This accesscheck works on serveral levels.
     * Level 0 - App: Tries to check access on possible app wide access function.
     * Level 1 - Controller: Tries to check access by looking for access setting in the controller itself.
     *
     * @param bool $force
     *            Set this to true if you want to force a brutal stop
     *
     * @return boolean
     */
    final protected function checkControllerAccess($force = false)
    {
        // Is there an global access method in the app main class to call?
        if (method_exists($this->app, 'Access') && $this->app->Access() === false) {
            return false;
        }

        // ACL set?
        if (isset($this->access)) {

            $perm = [];

            // Global access for all actions?
            if (isset($this->access['*'])) {
                if (! is_array($this->access['*'])) {
                    $perm[] = $this->access['*'];
                }
                else {
                    $perm += $this->access['*'];
                }
            }

            // Actions access set?
            if (isset($this->access[$this->action])) {
                if (! is_array($this->access[$this->action])) {
                    $perm[] = $this->access[$this->action];
                }
                else {
                    $perm += $this->access[$this->action];
                }
            }

            // Check the permissions against the current user
            if ($perm) {
                return $this->checkAccess($perm, $force);
            }
        }

        // Not set ACL or falling through here grants access by default
        return true;
    }

    /**
     * Set the name of the actiuon to rander
     *
     * By default this is the current controller action name and do not have to be set manually.
     *
     * @param string $action
     */
    final protected function setRenderAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Publish a value to the view
     *
     * @param string|array $arg1
     *            Name of var or list of vars in an array
     * @param mixed $arg2
     *            Optional value to be ste when $arg1 is the name of a var
     *
     * @throws ControllerException
     *
     * @return Controller
     */
    final protected function setVar($arg1, $arg2 = null)
    {
        // On non existing view we do not have to set anything
        if (property_exists($this, 'has_no_view')) {
            return;
        }

        if ($this->view === false || ! $this->view instanceof View) {
            $this->view = $this->app->getView($this->name);
        }

        // Some vars are protected and not allowed to be used outside the framework
        $protected_var_names = [
            'app',
            'controller',
            'action',
            'view',
            'model',
            'cfg'
        ];

        // One argument has to be an assoc array
        if (! isset($arg2) && is_array($arg1)) {
            foreach ($arg1 as $var => $value) {
                $this->view->setVar($var, $value);
            }
        }
        elseif (isset($arg2)) {
            $this->view->setVar($arg1, $arg2);
        }
        else {
            Throw new ControllerException('The vars to set are not correct.', 1001);
        }

        return $this;
    }

    /**
     * Returns value of a set var in view
     *
     * When var is not found an ControllerException is thrown by the view.
     *
     * @param sting $name
     *            Name of the var to get value from
     *
     * @return mixed
     */
    final protected function getVar($name)
    {
        return $this->view->getVar($name);
    }

    /**
     * Shorthand method for a FormDesigner instance with auto attached model
     *
     * @return FormDesigner
     */
    final protected function getFormDesigner($id = '')
    {
        /* @var $fd \Core\Html\FormDesigner\FormDesigner */
        $fd = $this->html->create('FormDesigner\FormDesigner');

        // Generate form id when id is not provided
        if (! $id) {

            // get calling method name
            $dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

            $pieces = [
                $this->stringUncamelize($this->app->getName()),
                $this->stringUncamelize($this->name),
                isset($dbt[1]['function']) ? $this->stringUncamelize($dbt[1]['function']) : null
            ];

            $id = implode('-', $pieces);
        }

        if ($id) {
            $fd->setId($id);
        }

        // Create forms eaction url
        $action = $this->url($this->router->getCurrentRoute(), $this->router->getParam());

        $fd->html->setAction($action);

        return $fd;
    }

    /**
     * Adds a paramter to the controllers parameter collection.
     *
     * Useful when redirecting to other controller action
     * which need additional parameters to function.
     *
     * @param string $params
     *            Paramertername
     * @param mixed $value
     *            Parametervalue
     *
     * @return \Core\Amvc\Controller
     */
    final protected function addParam($param, $value)
    {
        $this->params[$param] = $value;

        return $this;
    }

    /**
     * Sets the selector name to where the result is ajaxed.
     *
     * @param string $target
     *
     * @return \Core\Amvc\Controller
     */
    final protected function setAjaxTarget($target)
    {
        if ($this->ajax_flag) {
            $this->ajax_command->setSelector($target);
        }

        return $this;
    }

    /**
     * Sets the function to use when result is returned.
     *
     * @param string $function
     *
     * @return \Core\Amvc\Controller
     */
    final protected function setAjaxFunction($function)
    {
        if ($this->ajax_flag) {
            $this->ajax_command->setFunction($function);
        }

        return $this;
    }

    /**
     * Returns an empty ajax command object
     *
     * @param string $command_name
     *            Name of command to get. Default: Dom\Html
     *
     * @return \Core\Ajax\AjaxCommand
     */
    final public function getAjaxCommand($command_name = 'Dom\Html')
    {
        return $this->ajax->createCommand($command_name);
    }

    /**
     * Redirect function to make sure the browser doesn't come back and repost the form data
     *
     * @param string $location
     *            Location we redirtect to
     * @param bool $permanent
     *            Is this a permanent redirection?
     */
    final protected function redirectExit($location = '', $permanent = false)
    {
        if (empty($location)) {
            $location = BASEURL;
        }

        if (preg_match('~^(ftp|http)[s]?://~', $location) == 0 && substr($location, 0, 6) != 'about:') {
            $location = BASEURL . $location;
        }

        // Append session id
        // $location = preg_replace('/^' . preg_quote(BASEURL, '/') . '(?!\?' . preg_quote(SID, '/') . ')\\??/', BASEURL
        // . '?' . SID . ';', $location);

        $this->http->header->location($location, $permanent);
    }

    /**
     * Sets the output format.
     *
     * @param string $format
     *            Set request format. Allowed formats are: json, xml and html.
     */
    final public function setFormat($format)
    {
        $formats = [
            'file',
            'json',
            'xml',
            'html'
        ];

        if (! in_array($format, $formats)) {
            throw new ControllerException(sprintf('Format "%s" is not allowed. Please use one of the following formats: %s'), $format, implode(', ', $formats));
        }

        $this->router->setFormat($format);

        return $this;
    }

    /**
     * Dummy method for those who forget to create such method in their controller.
     */
    public function Index()
    {}
}
