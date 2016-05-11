<?php
namespace AppsSec\Core\Controller;

use Core\Amvc\Controller;
use Core\Security\Users;

class UserController extends Controller
{

    protected $access = [
        'Index' => 'admin',
        'Userlist' => 'admin',
        'Edit' => 'admin'
    ];

    /**
     *
     * @var UserModel
     */
    public $model;

    public function Index()
    {
        $this->redirect('Userlist');
    }

    public function Userlist()
    {
        $this->setVar([
            'userlist' => $this->model->getList('display_name', '%', 100, [
                [
                    function ($data) {
                        $data['link'] = $this->url('generic.id', [
                            'controller' => 'user',
                            'action' => 'edit',
                            'id' => $data['id_user']
                        ]);
                        return $data;
                    }
                ]
            ]),
            'links' => [
                'new' => [
                    'text' => $this->text->get('user.action.new.text'),
                    'url' => $this->url('generic.action', [
                        'controller' => 'user',
                        'action' => 'edit'
                    ])
                ]
            ],
            'text' => [
                'headline' => $this->text->get('user.list'),
                'username' => $this->text->get('user.field.username'),
                'display_name' => $this->text->get('user.field.display_name')
            ]
        ]);

        $this->setAjaxTarget('#core-admin');
    }

    public function Edit($id = null)
    {
        if (! $id) {
            $id = $this->security->user->getId();
        }

        $data = $this->http->post->get();

        if ($data) {

            $this->model->save($data);

            if (! $this->model->hasErrors()) {
                $this->redirect('Detail', [
                    'id' => $id
                ]);
                return;
            }
        }

        if (! $data) {
            $data = $this->model->getEdit($this->security->user, $id);
        }

        // Get FormDesigner object
        $fd = $this->getFormDesigner('core-user-edit');

        $fd->mapData($data);
        $fd->mapErrors($this->model->getErrors());

        // Flag form to be ajax
        $fd->isAjax();

        // Start new group for controls
        $group = $fd->addGroup();

        // Add hidden field for invoice id
        $group->addControl('hidden', 'id_user');

        // Username
        $control = $group->addControl('text', 'username');

        // Displayname
        $control = $group->addControl('text', 'display_name');

        // Usergroups
        $heading = $group->addElement('Elements\Heading');
        $heading->setSize(3);
        $heading->setInner($this->text->get('user.field.groups'));

        $groups = $this->security->group->getGroups();

        /* @var $control \Core\Html\Controls\Optiongroup */
        $control = $group->addControl('Optiongroup');
        $control->addCss('well well-sm');

        foreach ($groups as $app => $app_groups) {

            $control->createHeading($app);

            foreach ($app_groups as $id_group => $group) {

                // Skip guest and user group because guest is everyone unregisterted and user
                // everyone registered
                if ($id_group == - 1 || $id_group == 2) {
                    continue;
                }

                $option = $control->createOption();
                $option->setValue($id_group);
                $option->setInner($group['display_name']);

                if (array_key_exists($id_group, $data['groups'])) {
                    $option->isChecked();
                }
            }
        }

        // Remove core groups
        unset($groups['Core']);

        // Display all
        foreach ($groups as $app => $group) {}

        /* @var $editbox \Core\Html\Controls\Editbox */
        $editbox = $this->html->create('Controls\Editbox');
        $editbox->setForm($fd);

        // Editbox caption
        $editbox->setCaption($this->text->get('user.action.edit.text'));

        // Cancel action only when requested
        $editbox->setCancelAction($this->url('generic.id', [
            'controller' => 'User',
            'action' => 'Detail',
            'id' => $id
        ]));

        // Publish to view
        $this->setVar([
            'form' => $editbox
        ]);

        $this->setAjaxTarget('#core-admin');
    }

    public function Register()
    {
        $data = $this->http->post->get();

        if ($data) {

            // Get activation mode
            $activate = $this->cfg->get('user.activation.use');

            // Usercreationprocess which returns the id of the new user
            $id_user = $this->model->createUser($data, $activate);

            if (! $this->model->hasErrors()) {

                // Activation by mail?
                if ($activate===1) {

                    // Create combined key from activation data of user
                    $key = $this->token->createActivationToken($id_user, $this->cfg->Core->get('user.mail.activation.ttl'));

                    /* @var $mailer \Core\Mailer\Mailer */
                    $mailer = $this->di->get('core.mailer');

                    $mail = $mailer->createMail();
                    $mail->isHtml(true);
                    $mail->setMTA($this->cfg->get('user.mail.activation.mta'));

                    // Add user as recipient
                    $mail->addRecipient('to', $data['username']);

                    // Get from address and name from config as sender informations
                    $from = $this->cfg->get('user.mail.activation.from');
                    $name = $this->cfg->get('user.mailactivation.name');

                    $mail->setFrom($from, $name);

                    // Define strings to replace placeholder in mailtexts
                    $strings = [
                        'brand' => $this->cfg->get('site.general.name'),
                        'url.activate' => $this->url('activate', [
                            'action' => 'activate',
                            'key' => $key
                        ]),
                        'url.deny' => $this->url('activate', [
                            'action' => 'deny',
                            'key' => $key
                        ])
                    ];

                    // Create subject by replacing {brand} placeholder strings
                    $subject = str_replace('{brand}', $strings['brand'], $this->text->get('register.mail.subject'));

                    // Add subject as title string to replace a placeholder+
                    $strings['title'] = $subject;

                    // Create html related stuff like <html>, <head> and <body> wrapping the body content
                    $body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>{title}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body>' . $this->text->get('register.mail.body.html') . '</body>

</html>';

                    $alt_body = $this->text->get('register.mail.body.plain');

                    // Replace placeholder
                    foreach ($strings as $key => $val) {
                        $body = str_replace('{' . $key . '}', $val, $body);
                        $alt_body = str_replace('{' . $key . '}', $val, $alt_body);
                    }

                    $mail->setSubject($subject);
                    $mail->setBody($body);
                    $mail->setAltbody($alt_body);

                    $state = 'wait';
                }
                else {
                    $state = 'done';
                }

                $this->redirect('AccountState', [
                    'state' => $state
                ]);

                return;
            }
        }

        if (empty($data)) {
            $data = $this->model->getRegister();
        }

        $fd = $this->getFormDesigner('core-register-user');
        $fd->mapData($data);
        $fd->mapErrors($this->model->getErrors());

        $group = $fd->addGroup();

        $username = $group->addControl('Text', 'username');

        $text = $this->text->get('register.form.username');
        $username->setPlaceholder($text);
        $username->noLabel();

        $password = $group->addControl('Password', 'password');

        $text = $this->text->get('register.form.password');
        $password->setPlaceholder($text);
        $password->noLabel();

        if ($this->cfg->get('user.register.use_compare_password')) {
            $password_compare = $group->addControl('Password', 'password_compare');

            $text = $this->text->get('register.form.compare');
            $password_compare->setPlaceholder($text);
            $password_compare->noLabel();
        }

        $btn_group_just = $group->addGroup();
        $btn_group_just->html->addCss('btn-group btn-group-sm btn-group-justified');

        $btn_group = $btn_group_just->addGroup();
        $btn_group->html->addCss('btn-group');

        $control = $btn_group->addControl('submit');
        $control->setUnbound();

        $icon = $this->html->create('Elements\Icon');
        $icon->useIcon('key');

        $control->setInner($icon->build() . ' ' . $this->text->get('register.form.button'));

        $this->setVar([
            'headline' => $this->text->get('register.form.headline'),
            'form' => $fd,
            'state' => 0
        ]);
    }

    public function AccountState($state = 'wait')
    {
        $this->setVar([
            'headline' => $this->text->get('register.activation.' . $state . '.headline'),
            'text' => $this->text->get('register.activation.' . $state . '.text')
        ]);
    }

    public function Activate($key)
    {
        // $key = $this->router->getParam('key');
        $id_user = $this->security->users->activateUser($key);

        // Redirect to RegisterDone on successfull activation
        if (! empty($id_user)) {
            $this->redirectExit($this->url('generic.action', [
                'controller' => 'user',
                'action' => 'done'
            ]));
            return;
        }

        $this->redirect('AccountState', [
            'state' => 'fail'
        ]);
    }

    Public function Deny($key)
    {
        $this->redirect('AccountState', [
            'state' => $this->security->users->denyActivation($key) ? 'deny.ok' : 'deny.nouser'
        ]);
    }
}

