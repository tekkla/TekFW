<?php
namespace Core\AppsSec\Core\Controller;

use Core\AppsSec\Core\Model\SecurityModel;
use Core\Lib\Amvc\Controller;

/**
 * SecurityController.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class LoginController extends Controller
{

    /**
     *
     * @var SecurityModel
     */
    public $model;

    /**
     *
     * @throws \Core\Lib\Errors\Exceptions\InvalidArgumentException
     */
    public function Login()
    {
        if ($this->security->loggedIn()) {
            $this->redirect('AlreadyLoggedIn');
            return;
        }

        if ($this->security->checkBan()) {
            $this->redirectExit($this->url('index'));
        }

        $data = $this->post->get();

        if ($data) {

            // Do login procedure
            $logged_in = $this->model->doLogin($data);

            //
            if ($logged_in == true) {
                $url = $this->url('index');
                $this->redirectExit($url);
            }
            else {
                $_SESSION['login_failed'] =  true;
                $this->message->danger($this->text('login.failed'));
            }
        }
        else {
            $data = $this->model->getEmptyLogin();
        }

        $form = $this->getFormDesigner($data);
        $form->setName('core-login');

        if (isset($_SESSION['display_activation_notice'])) {
            $group = $form->addGroup();
            $group->addCss('alert alert-info');
            $group->setRole('alert');
            $group->setInner($this->text('register.activation.notice'));
        }

        // Create element group
        $group = $form->addGroup();

        /* @var $control \Core\Lib\Html\FormDesigner\Controls\TextControl */
        $control = $group->addControl('Text', 'login');

        $text = $this->text('login.username');
        $control->setPlaceholder($text);
        $control->noLabel();

        /* @var $control \Core\Lib\Html\FormDesigner\Controls\TextControl */
        $control = $group->addControl('Password', 'password');

        $text = $this->text('login.password');
        $control->setPlaceholder($text);
        $control->noLabel();

        $group = $form->addGroup();

        /* @var $control \Core\Lib\Html\Form\Checkbox */
        $control = $group->addControl('Checkbox', 'remember');
        $control->setValue(1);
        $control->setLabel($this->text('login.remember_me'));

        if ($this->cfg('security.login.autologin')) {
            $control->isChecked();
        }

        $btn_group = $form->addGroup();
        $btn_group->setId('btn-group');
        $btn_group->addCss([
            'btn-group',
            'btn-group-sm',
            'btn-group-justified'
        ]);

        $btn_group_button = $btn_group->addGroup();
        $btn_group->setId('btn-group-button');
        $btn_group_button->addCss([
            'btn-group'
        ]);

        $control = $btn_group_button->addControl('Submit');

        $icon = $this->getHtmlObject('Elements\Icon');
        $icon->useIcon('key');

        $control->setInner($icon->build() . ' ' . $this->text('user.action.login'));

        $btn_group_button = $btn_group->addGroup();
        $btn_group_button->addCss([
            'btn-group'
        ]);

        $control = $btn_group_button->addControl('Submit');
        $control->setInner($this->text('user.action.reset'));

        $this->setVar([
            'headline' => $this->text('user.action.login'),
            'form' => $form
        ]);

        $this->page->breadcrumbs->createActiveItem($this->text('user.action.login'));
    }

    public function Logout()
    {
        $this->security->logout();
        $this->redirectExit($this->router->url('core_index'));
    }

    public function AlreadyLoggedIn()
    {
        $this->setVar('loggedin', $this->text('already_loggedin'));
    }
}
