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
                $this->redirectExit();
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

        // Form save button
        $form->setSaveButtonText($this->txt('login'));
        $form->setIcon('submit', 'key');

        // Create element group
        $group = $form->addGroup();

        /* @var $control \Core\Lib\Content\Html\FormDesigner\Controls\TextControl */
        $control = $group->addControl('Text', 'login');
        $control->noLabel();
        $control->setPlaceholder($this->txt('username'));

        /* @var $control \Core\Lib\Content\Html\FormDesigner\Controls\TextControl */
        $control = $group->addControl('Password', 'password');
        $control->noLabel();
        $control->setPlaceholder($this->txt('password'));

        /* @var $control \Core\Lib\Content\Html\Form\Checkbox */
        $control = $group->addControl('Checkbox', 'remember');
        $control->setValue(1);
        $control->setLabel($this->txt('remember_me'));

        $this->setVar('form', $form);

        $this->content->breadcrumbs->createActiveItem($this->txt('login'));
    }

    public function Logout()
    {
        $this->model->doLogout();
        $this->redirectExit($this->router->url('core_index'));
    }

    public function Register() {

        $data = $this->post->get();

        if ($data) {

            // Do login procedure
            $data = $this->model->saveUser($data);

            if (!$data->hasErrors()) {
                $this->content->msg->success($this->txt('login_ok'));
                $this->redirectExit($this->url('core_user', [$data['id_user']]));
            } else {
                $this->content->msg->danger($this->txt('login_failed'));
            }
        }
        else {
            $data = $this->model->getRegister($id_user);
        }
    }

}
