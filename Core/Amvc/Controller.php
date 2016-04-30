<?php
namespace Core\Amvc;

use Core\Router\Router;
use Core\Http\Http;
use Core\Security\Security;
use Core\Page\Page;
use Core\Html\HtmlFactory;
use Core\Html\FormDesigner\FormDesigner;
use Core\Ajax\Ajax;
use Core\Router\UrlTrait;
use Core\Traits\ArrayTrait;
use Core\Cfg\CfgTrait;
use Core\Language\TextTrait;
use Core\IO\IO;
use Core\Ajax\Dom;

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
     * Action to call.
     * Default: Index
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
     * Redirection definition
     *
     * @var array
     */
    private $redirect = [];

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
     * Storage for vars to be sent to view renderer
     *
     * @var array
     */
    protected $vars = [];

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
     *
     * @var Dom
     */
    private $ajax_cmd;

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
        // Use givem params
        if (empty($action)) {
            Throw new ControllerException(sprintf('The action name for %s::run() is empty.', $this->name));
        }

        $this->action = $action;

        // If accesscheck failed => stop here and return false!
        if ($this->checkControllerAccess() == false) {
            $this->page->message->warning($this->text('access.missing_userrights'));
            $this->log->suspicious(sprintf('Missing permission for ressource %s.%s.%s', $this->app->getName(), $this->getName(), $action));
            return false;
        }

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

        // Redirect initiated from within the called action?
        if (! empty($this->redirect)) {

            // Clean post data wanted?
            if ($this->redirect[2] == true) {
                $this->http->post->clean();
            }

            // Target is an array and will be analyzed and used as app, controller and actionnames
            if (is_array($this->redirect[0])) {

                // Make sure we have all essential data before calling ACA
                $require = [
                    'app',
                    'controller',
                    'action'
                ];

                foreach ($require as $check) {
                    if (empty($this->redirect[0][$check])) {
                        Throw new ControllerException(sprintf('Redirects by arrayed $target need a set "%s" element.', $check));
                    }
                }

                $app = $this->app->creator->getAppInstance($this->redirect[0]['app']);
                $controller = $app->getController($this->redirect[0]['controller']);

                $return = $controller->run($this->redirect[0]['action'], $this->redirect[1]);

                // Prevent rendering of this controllers view because rendering results
                // are coming from the redirected ACA
                $this->render = false;

                // Reset redirection settings
                $this->redirect = [];
            }

            // Target is a string and is treated as action name
            else {

                // It is important to clean the redirection property before calling the redirection action in this
                // controller. Without cleaning it an endless redirect recursion occurs.
                $action = $this->redirect[0];
                $params = $this->redirect[1];

                $this->redirect = [];

                $return = $this->run($action, $params);
            }
        }

        // Do we have a result?
        if (isset($return)) {

            // Boolean false result means to stop work for controller
            if ($return == false) {
                return false;
            }

            // Control rendering by requested output format
            $no_render_format = [
                'json',
                'xml',
                'css',
                'js',
                'file'
            ];

            if (in_array($this->router->getFormat(), $no_render_format)) {
                $this->render = false;
            }
        }

        // Render the view and return the result
        if ($this->render === true) {

            // Create view instance if not alredy done
            if (! $this->view instanceof View) {
                $this->view = $this->app->getView($this->name);
            }

            // Check if there still no view object
            if (empty($this->view)) {
                Throw new ViewException(sprintf('A result has to be rendered but "%sView" does not exist.', $this->name));
            }

            // Render
            $content = $this->view->render($this->action, $this->params, $this->vars);

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
        // Prepare a fresh ajax command object
        $this->ajax_cmd = $this->ajax->createDomCommand();

        // Get content from Controller::run()
        $content = $this->run($action, $params);

        if ($content !== false) {

            $this->ajax_cmd->setArgs($content);
            $this->ajax_cmd->setId(get_called_class() . '::' . $action);

            if (empty($this->ajax_cmd->getSelector()) && ! empty($selector)) {
                $this->ajax_cmd->setSelector($selector);
            }

            $this->ajax->addCommand($this->ajax_cmd);
        }

        // Add messages
        $messages = $this->page->message->getAll();

        if ($messages) {

            foreach ($messages as $msg) {

                /* @var $cmd \Core\Ajax\Dom */
                $cmd = $this->ajax->createDomCommand();

                $msg_selector = '#core-message';

                if ($msg->getType() == 'clear') {
                    $cmd->clear($msg_selector);
                    continue;
                }

                $html = '
                <div class="alert alert-' . $msg->getType();

                // Message dismissable?
                if ($msg->getDismissable()) {
                    $html .= ' alert-dismissable';
                }

                // Fadeout message?
                if ($this->cfg('js.style.fadeout_time', 'Core') > 0 && $msg->getFadeout()) {
                    $html .= ' fadeout';
                }

                $html .= '">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    ' . $msg->getMessage() . '
                    </div>';

                $cmd->append($msg_selector, $html);

                $this->ajax->addCommand($cmd);
            }
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
     */
    final protected function redirect($target, $params = [], $clear_post = true)
    {
        $this->redirect = [
            $target,
            $params,
            $clear_post
        ];

        return $this;
    }

    /**
     * Does an urlredirect but cares about what kind (ajax?) of request was send.
     *
     * @param Url|string $url
     */
    final protected function doRefresh($url)
    {
        if ($this->router->isAjax()) {
            $cmd = $this->ajax->createActCommand();

            $this->ajax->refresh($url);
        }
        else {
            $this->redirectExit($url);
        }
    }

    /**
     * Checks the controller access of the user
     *
     * This accesscheck works on serveral levels.
     *
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
        if (! empty($this->access)) {

            $perm = [];

            // Global access for all actions?
            if (array_key_exists('*', $this->access)) {
                if (! is_array($this->access['*'])) {
                    $this->access['*'] = (array) $this->access['*'];
                }

                $perm += $this->access['*'];
            }

            // Actions access set?
            if (isset($this->access[$this->action])) {
                if (! is_array($this->access[$this->action])) {
                    $this->access[$this->action] = (array) $this->access[$this->action];
                }

                $perm += $this->access[$this->action];
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
        // One argument has to be an assoc array
        if (! isset($arg2) && is_array($arg1)) {
            foreach ($arg1 as $var => $value) {
                $this->vars[$var] = $this->varHandleObject($value);
            }
        }
        elseif (isset($arg2)) {
            $this->vars[$arg1] = $this->varHandleObject($arg2);
        }
        else {
            Throw new ControllerException('The vars to set are not correct.', 1001);
        }

        return $this;
    }

    private function varHandleObject($val)
    {

        // Handle objects
        if (is_object($val)) {

            switch (true) {

                // Handle buildable objects
                case method_exists($val, 'build'):
                    $val = $val->build();
                    break;

                // Handle all other objects
                default:
                    $val = get_object_vars($val);
                    break;
            }
        }

        return $val;
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
     * Sets the selector name to where the result is ajaxed.
     *
     * @param string $target
     *
     * @return \Core\Amvc\Controller
     */
    final protected function setAjaxTarget($target)
    {
        if (!empty($this->ajax_cmd)) {
            $this->ajax_cmd->setSelector($target);
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
        if (!empty($this->ajax_cmd)) {
            $this->ajax_cmd->setFunction($function);
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
        // No view rendering!
        $this->render = false;

        if (empty($location)) {
            $location = BASEURL;
        }

        if (preg_match('~^(ftp|http)[s]?://~', $location) == 0 && substr($location, 0, 6) != 'about:') {
            $location = BASEURL . $location;
        }

        $_SESSION['Core']['redirect'] = [
            'location' => $location,
            'permanent' => $permanent
        ];
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
