<?php
namespace Core\AppsSec\Core\Controller;

use Core\Lib\Amvc\Controller;

/**
 * Appsec/Core/SecurityController
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license MIT
 */
class SecurityController extends Controller
{

    public function Login()
    {
        $data = $this->post->get();

        if ($data) {

            // Do login procedure
            $data = $this->model->doLogin($data);

            if ($data['logged_in'] === true) {
                $this->content->msg->success($this->txt('login_ok'));
                $this->redirectExit();
            } else {
                $this->content->msg->danger($this->txt('login_failed'));
            }
        }
        else {

            $data = $this->model->getEmptyLogin();
        }

        $form = $this->getFormDesigner($data);

        $form->setApp('Core');
        $form->setModelName('Security');

        $action = $this->router->url('core_login');
        $form->setAction($action);

        /* @var $control \Core\Lib\Content\Html\Form\Input */
        $control = $form->createElement('input', 'login');
        $control->noLabel();
        $control->setPlaceholder($this->txt('username'));

        /* @var $control \Core\Lib\Content\Html\Form\Input */
        $control = $form->createElement('password', 'password');
        $control->noLabel();
        $control->setPlaceholder($this->txt('password'));

        /* @var $control \Core\Lib\Content\Html\Form\Checkbox */
        $control = $form->createElement('checkbox', 'remember');
        $control->setValue(1);
        $control->setLabel($this->txt('remember_me'));

        $form->setSaveButtonText($this->txt('login'));
        $form->setIcon('submit', 'key');

        $this->setVar('form', $form);

        $this->content->breadcrumbs->createActiveItem($this->txt('login'));
    }

    public function Logout()
    {
        $this->model->doLogout();
        $this->redirectExit($this->router->url('core_index'));
    }
}
