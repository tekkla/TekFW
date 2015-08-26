<?php
namespace Core\AppsSec\Core\Controller;

use Core\Lib\Amvc\Controller;

/**
 * SecurityController.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class SecurityController extends Controller
{

    public function Login()
    {
        if ($this->security->loggedIn()) {
            $this->redirect('AlreadyLoggedIn');
            return;
        }

        $data = $this->post->get();

        if ($data) {

            // Do login procedure
            $logged_in = $this->model->doLogin($data);

            //
            if ($logged_in == true) {
                $url = $this->url('index');
                $this->redirectExit($url);
                return;
            }
            else {
                $this->message->danger($this->txt('login_failed'));
            }
        }
        else {
            $data = $this->model->getEmptyLogin();
        }

        $form = $this->getFormDesigner($data);

        $form->setAction($this->url('login'));

        // Form save button
        $form->setSaveButtonText($this->txt('login'));
        $form->setIcon('submit', 'key');

        // Create element group
        $group = $form->addGroup();

        /* @var $control \Core\Lib\Content\Html\FormDesigner\Controls\TextControl */
        $control = $group->addControl('Text', 'login');
        $control->setPlaceholder($this->txt('username'));
        $control->setLabel($this->txt('username'));

        /* @var $control \Core\Lib\Content\Html\FormDesigner\Controls\TextControl */
        $control = $group->addControl('Password', 'password');
        $control->setPlaceholder($this->txt('password'));
        $control->setLabel($this->txt('password'));

        /* @var $control \Core\Lib\Content\Html\Form\Checkbox */
        $control = $group->addControl('Checkbox', 'remember');
        $control->setValue(1);
        $control->setLabel($this->txt('remember_me'));

        $this->setVar('form', $form);

        $this->content->breadcrumbs->createActiveItem($this->txt('login'));
    }

    public function Logout()
    {
        $this->security->logout();
        $this->redirectExit($this->router->url('core_index'));
    }

    public function AlreadyLoggedIn()
    {
        $this->setVar('loggedin', $this->txt('already_loggedin'));
    }
}
