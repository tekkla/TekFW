<?php
namespace Core\AppsSec\Core\Controller;

use Core\Lib\Amvc\Controller;

/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 */
class SecurityController extends Controller
{

	public $has_no_model;

	public function Login()
	{
		$post = $this->post->get();

		if ($post) {

			// Do login procedure
			$this->security->login($post->login, $post->password);

			if ($this->security->loggedIn()) {
				$this->message->success('Login OK!');
				$this->redirectExit();
			} else {
				$this->message->error('Login Failed');
			}
		}

		$form = $this->getFormDesigner();

		$form->setApp('Core');
		$form->setModelName('Security');

		$action = $this->router->url('core_login');
		$form->setAction($action);

		/* @var $control \Core\Lib\Content\Html\Form\Input */
		$control = $form->createElement('input', 'login');
		$control->noLabel();
		$control->setPlaceholder('Username');

		/* @var $control \Core\Lib\Content\Html\Form\Input */
		$control = $form->createElement('password', 'password');
		$control->noLabel();
		$control->setPlaceholder('Password');

		/* @var $control \Core\Lib\Content\Html\Form\Checkbox */
		$control = $form->createElement('checkbox', 'remember');
		$control->setLabel('Remember me');

		$form->setSaveButtonText('Login');
		$form->setIcon('submit', 'key');

		$this->setVar('form', $form);

		$this->content->breadcrumbs->createActiveItem('login');
	}

	public function Logout()
	{
		$this->security->logout();
		$this->redirectExit($this->router->url('core_index'));
	}
}
