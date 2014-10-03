<?php
namespace Core\Lib\Amvc;

use Core\Lib\Abstracts\MvcAbstract;
use Core\Lib\Request;
use Core\Lib\Security\Security;
use Core\Lib\Content\Page;
use Core\Lib\Content\Url;
use Core\Lib\Content\Message;
use Core\Lib\Content\LinktreeElement;

/**
 * Controllers parent class.
 * Each app controller has to be a child of this class.
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 *
 */
class Controller extends MvcAbstract
{

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
	 * Action to render
	 *
	 * @var String
	 */
	private $action = 'Index';

	/**
	 * Storage for access rights
	 *
	 * @var array
	 */
	protected $access = [];

	/**
	 * Storage for events
	 *
	 * @var array
	 */
	protected $events = [];

	/**
	 * The View object
	 *
	 * @var View
	 */
	public $view = false;

	/**
	 * Storage for parameter
	 *
	 * @var Data
	 */
	private $param = [];

	/**
	 * Stores the controller bound Model object.
	 * Is false when controller has no model.
	 *
	 * @var Model
	 */
	public $model;

	/**
	 * Falg to signal that this controller is in ajax mode
	 *
	 * @var bool
	 */
	private $is_ajax = false;

	/**
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 *
	 * @var Security
	 */
	protected $security;

	/**
	 *
	 * @var Message
	 */
	protected $message;

	/**
	 *
	 * @var Page
	 */
	protected $page;

	/**
	 *
	 * @var url
	 */
	protected $url;

	/**
	 * Hidden constructor.
	 * Runs the onLoad eventmethod and inits the internal view and model.
	 */
	final public function __construct($name, App $app, Request $request, Security $security, Message $message, Page $page, Url $url)
	{
		// Store name
		$this->name = $name;
		$this->app = $app;
		$this->request = $request;
		$this->security = $security;
		$this->message = $message;
		$this->page = $page;
		$this->url = $url;

		// Model to bind?
		$this->model = property_exists($this, 'has_no_model') ? false : $this->app->getModel($name);

		// Run onload event
		$this->runEvent('load');
	}

	/**
	 * Access the apps config data.
	 * Setting one parameter means you want to read a value. Both param writes a config value.
	 *
	 * @param string $key Config to get
	 * @param mixed $val Value to set in the apps config
	 * @return mixed config value
	 */
	final protected function cfg($key = null, $val = null)
	{
		return $this->app->cfg($key, $val);
	}

	/**
	 * Runs the requested controller action.´
	 *
	 * This is THE run method for each controller in each app. When used to resolve
	 * requests, no action or parameter need to be set. Both will be autodiscovered
	 * by requesting the needed data from the requesthandler. The method can also be
	 * used to get a partial result when using a controller from within a controller.
	 * In this case you should provide the action an parameters needed to get the
	 * wanted result.
	 *
	 * @param string $action Optional action to run
	 * @param string $param Parameter to use
	 * @return boolean bool|string
	 * @todo Controller access is deactivated
	 */
	final public function run($action, $param = [])
	{
		$this->action = $action;

		// If accesscheck failed => stop here and return false!
		/**
		 *
		 * @todo Does not work now. Important!!!
		 */
		if ($this->checkControllerAccess() == false) {
			return true;
		}

		// Try to autodiscover params on empty param args
		if (! empty($param)) {
			$this->param = $param;
		}

		// Init return var with boolean false as default value. This default
		// prevents from running the views render() method when the controller
		// action is stopped manually by using return.
		$return = false;

		// run possible before event handler
		$this->runEvent('before');

		// a little bit of reflection magic to pass request param into controller func
		$return = $this->di->invokeMethod($this, $this->action, $this->param);

		// run possible after event handler
		$this->runEvent('after');

		// No result to return? Return false
		if (isset($return) && $return == false) {
			return false;
		}

		// Render the view and return the result
		if ($this->render === true) {

			// Create view instance if not alredy done
			if (! $this->view instanceof View) {
				$this->view = $this->app->getView($this->name);
			}

			// Render into own outputbuffer
			ob_start();
			$this->view->render($this->action, $this->param);
			$content = ob_get_clean();

			// Run possible onEmpty event of app on no render result
			if (empty($content) && method_exists($this->app, 'onEmpty')) {
				$content = $this->app->onEmpty();
			}

			return $content;
		}
	}

	/**
	 * Ajax method to send the result of an action a ajax html command.
	 * This works similiar to the run() method and even uses it. The difference
	 * is that the renderesult is packed into an ajax command object. The ajax
	 * command can be controlled by setting the wanted parameters via $this->ajax->...
	 *
	 * @param string $action Name of the action to call
	 * @param array $param Array of parameter to be used in action call
	 * @param string $selector Optional jQuery selector to html() the result.
	 *        Can be overridden by setAjaxTarget() method
	 */
	final public function ajax($action = 'Index', $param = [], $selector = '')
	{
		$this->is_ajax = true;

		$this->ajax = $this->di['core.content.ajax'];

		if ($selector) {
			$this->ajax->setSelector($selector);
		}

		$content = $this->run($action, $param);

		if ($content) {
			$this->ajax->setArgs($content);
			$this->ajax->add();
		}

		return $this;
	}

	/**
	 * Redirects from one action to another
	 * When redirecting is used the attached models data will be reset and all
	 * post data from the request handler.
	 *
	 * @param string $action
	 * @param array $param
	 */
	final protected function redirect($action, $param = [])
	{
		// Clean data
		$this->cleanUp();

		// Run redirect method
		return $this->run($action, $param);
	}

	/**
	 * Method to cleanup data in controllers model and the request handler
	 *
	 * @param bool $model Flag to clean model data (default: true)
	 * @param string $post Flag to clean post data (default: true)
	 */
	final protected function cleanUp($model = true, $post = true)
	{
		// Reset model data
		if ($model && isset($this->model)) {
			$this->model->reset(true);
		}

		// Reset post data
		if ($post) {
			$this->request->clearPost();
		}
	}

	/**
	 * Event handler
	 *
	 * @param string $event
	 * @return \Core\Lib\Amvc\Controller
	 */
	private function runEvent($event)
	{
		if (! empty($this->events[$this->action][$event])) {
			if (! is_array($this->events[$this->action][$event])) {
				$this->events[$this->action][$event] = array(
					$this->events[$this->action][$event]
				);
			}

			foreach ($this->events[$this->action][$event] as $event_func) {
				$this->di->invokeMethod($this, $event_func, $this->request->getAllParams());
			}
		}

		return $this;
	}

	/**
	 * Loads the associated viewobject
	 *
	 * @param string $app Name of the views app
	 * @param string $view Name of the view
	 */
	final protected function loadView($view)
	{
		$this->view = View::factory($this->app, $view);
		return $this;
	}

	/**
	 * Does an urlredirect but cares about what kind (ajax?) of request was send.
	 *
	 * @param Url|string $url
	 */
	final protected function doRefresh($url)
	{
		if ($this->request->isAjax()) {
			$this->ajax->refresh($url);
			$this->firephp('Ajax refresh command set: ' . $url);
		} else
			$this->redirectExit($url);
	}

	/**
	 * Simple interface function for SMFs allowedTo() function
	 *
	 * @param string|array $perm
	 * @return boolean
	 */
	final protected function checkUserrights($perm)
	{
		return $this->security->checkAccess($perm);
	}

	/**
	 * Checks the controller access of the user.
	 * This accesscheck works on serveral levels.
	 * Level 0 - App: Tries to check access on possible app wide access function
	 * Level 1 - Controller: Tries to check access by looking for access setting in the controller itself.
	 *
	 * @param boolean $smf Use the SMF permission system. You should only deactivate this, if you have your own rightsmanagement
	 * @param bool $force Set this to true if you want to force a brutal stop
	 * @return boolean
	 */
	final protected function checkControllerAccess($mode = 'smf', $force = false)
	{
		// Is there an global access method in the app main class to call?
		if (method_exists($this->app, 'appAccess') && $this->app->appAccess() === false) {
			return false;
		}

		// ACL set?
		if (isset($this->access)) {
			$perm = [];

			// Global access for all actions?
			if (isset($this->access['*'])) {
				if (! is_array($this->access['*'])) {
					$perm[] = $this->access['*'];
				} else {
					$perm += $this->access['*'];
				}
			}

			// Actions access set?
			if (isset($this->access[$this->action])) {
				if (! is_array($this->access[$this->action])) {
					$perm[] = $this->access[$this->action];
				} else {
					$perm += $this->access[$this->action];
				}
			}

			// No perms until here means we can finish here and allow access by returning true
			if ($perm) {
				return $this->security->checkAccess($perm, $mode, $force);
			}
		}

		// Not set ACL or falling through here grants access by default
		return true;
	}

	/**
	 * Set the name of the actiuon to rander.
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
	 * @param string|array $arg1 Name of var or list of vars in an array
	 * @param mixed $arg2 Optional value to be ste when $arg1 is the name of a var
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
		} elseif (isset($arg2)) {
			$this->view->setVar($arg1, $arg2);
		} else
			Throw new \InvalidArgumentException('The vars to set are not correct.', 1001);

		return $this;
	}

	/**
	 * Set the meta title of the html output
	 *
	 * @param string $title
	 */
	final protected function setPageTitle($title)
	{
		$this->page->setTitle($title);
		return $this;
	}

	/**
	 * Set the meta description of the html output
	 *
	 * @param string $description
	 */
	final protected function setPageDescription($description)
	{
		$this->page->setDescription($description);
		return $this;
	}

	/**
	 * Shorthand method for adding a flash message.
	 *
	 * @param string $message
	 * @param string $type
	 */
	final protected function addMessage($message, $type)
	{
		$this->message{$type}($message);
		return $this;
	}

	/**
	 * Adds an entry to the SMF linktree.
	 * Label will be shown as text and URL
	 * is the link url. Url parameter can be an instance of Url. The url will
	 * be created automatic by using the url object getUrl() method.
	 *
	 * @param string $name
	 * @param string,Url $url
	 */
	final protected function addLinktree($label, $url = null)
	{
		$this->page->addLinktree(new LinktreeElement($label, $url));

		return $this;
	}

	/**
	 * Shorthand method for a FormDesigner instance with auto attached model
	 *
	 * @return FormDesigner
	 */
	final protected function getFormDesigner()
	{
		/* @var $form \Core\Helper\FormDesigner */
		$form = $this->di['core.helper.formdesigner'];

		if (!property_exists($this, 'has_no_model'))
			$form->attachModel($this->model);

		$form->setAction($this->url->compile($this->request->getCurrentRoute(), $this->param));

		return $form;
	}

	/**
	 * Wrapper method for $this->app->getController()
	 *
	 * @param string $control->ler_name
	 * @return Controller
	 */
	final protected function getController($controller_name)
	{
		return $this->app->getController($controller_name);
	}

	/**
	 * Wrapper method for $this->app->getModel()
	 *
	 * @param string $model_name
	 * @return \Core\Lib\Amvc\Model
	 */
	final protected function getModel($model_name)
	{
		return $this->app->getModel($model_name);
	}

	/**
	 * Adds a paramter to the controllers parameter collection.
	 * Useful when redirecting to other controller action
	 * which need additional parameters to function.
	 *
	 * @param string $param Paramertername
	 * @param mixed $value Parametervalue
	 */
	final protected function addParam($param, $value)
	{
		$this->param[$param] = $value;
		return $this;
	}

	/**
	 * Sets the selector name to where the result is ajaxed.
	 *
	 * @param string $target
	 * @return \Core\Lib\Amvc\Controller
	 */
	final protected function setAjaxTarget($target)
	{
		if ($this->is_ajax) {
			$this->ajax->setSelector($target);
		}

		return $this;
	}

	/**
	 * Redirect function to make sure the browser doesn't come back and repost the form data.
	 *
	 * @param string $location Location we redirtect to
	 * @param bool $refresh Use refresh instead of location
	 */
	final protected function redirectExit($location = '', $permanent = false)
	{
		if (! $location) {
			$location = BASEURL;
		}

		if (preg_match('~^(ftp|http)[s]?://~', $location) == 0 && substr($location, 0, 6) != 'about:') {
			$location = BASEURL . ($location != '' ? '?' . $location : '');
		}

		// Append session id
		$location = preg_replace('/^' . preg_quote(BASEURL, '/') . '(?!\?' . preg_quote(SID, '/') . ')\\??/', BASEURL . '?' . SID . ';', $location);

		header('Location: ' . str_replace(' ', '%20', $location), true, $permanent ? 301 : 302);
	}
}
