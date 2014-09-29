<?php

namespace Core\AppsSec\Core\Controller;

use Core\Lib\Amvc\Controller;

/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 */
class SecurityController extends Controller
{
	public function Login()
	{
		$post = $this->request->getPost();

		var_dump($post);

		if($post)
		{
			$id_user = $this->security->login($post->login, $post->password);

			if ($id_user)
				$this->message->success('Login OK!');
			else
				$this->message->error('Login Failed');
		}


		if ($this->security->loggedIn())
			$this->redirectExit();

		$form = $this->getFormDesigner();

		$action = $this->request->getRouteUrl('core_login');
		$form->setAction($action);

		/* @var $control-> \Core\Lib\Content\Html\Form\Input */
		$control = $form->createElement('input', 'login');
		$control->noLabel();
		$control->setPlaceholder('Username');

		/* @var $control-> \Core\Lib\Content\Html\Form\Input */
		$control = $form->createElement('password', 'password');
		$control->noLabel();
		$control->setPlaceholder('Password');

		/* @var $control-> \Core\Lib\Content\Html\Form\Checkbox */
		$control = $form->createElement('checkbox', 'remember');
		$control->setLabel('Remember me');

		$form->setSaveButtonText('Login');
		$form->setIcon('submit', 'key');

		$this->setVar('form', $form);
	}
}
