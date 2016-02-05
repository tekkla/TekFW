<?php
namespace Core\AppsSec\Core\Controller;

use Core\Lib\Amvc\Controller;

/**
 * UserController.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class RegisterController extends Controller
{

    /* @var $model \Core\AppSec\Core\Model\RegisterModel */
    public $model;

    public function Register()
    {
        $data = $this->post->get();
        
        if ($data) {
            
            $activate = $this->cfg('security.activation.use');
            
            $id_user = $this->model->register($data, $activate);
            
            if (! $data->hasErrors()) {
                
                if ($activate) {
                    
                    // Create combined key from activation data of user
                    $activation = $this->model->getActivationData($id_user);
                    $key = $activation['selector'] . ':' . $activation['token'];
                    
                    /* @var $mailer \Core\Lib\Mailer\Mailer */
                    $mailer = $this->di->get('core.mailer');
                    
                    $mail = $mailer->createMail();
                    $mail->isHtml(true);
                    $mail->setMTA($this->cfg('security.activation.mta'));
                    
                    // Add user as recipient
                    $mail->addRecipient('to', $data['username']);
                    
                    // Get from address and name from config as sender informations
                    $from = $this->cfg('security.activation.from');
                    $name = $this->cfg('security.activation.name');
                    
                    $mail->setFrom($from, $name);
                    
                    // Define strings to replace placeholder in mailtexts
                    $strings = [
                        'brand' => $this->page->getBrand(),
                        'url.activate' => $this->url('register.activation', [
                            'key' => $key
                        ]),
                        'url.deny' => $this->url('register.deny', [
                            'key' => $key
                        ])
                    ];
                    
                    // Create subject by replacing {brand} placeholder strings
                    $subject = str_replace('{brand}', $strings['brand'], $this->text('register.mail.subject'));
                    
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
<body>' . $this->text('register.mail.body.html') . '</body>

</html>';
                    
                    $alt_body = $this->text('register.mail.body.alt');
                    
                    // Replace placeholder
                    foreach ($strings as $key => $val) {
                        $body = str_replace('{' . $key . '}', $val, $body);
                        $alt_body = str_replace('{' . $key . '}', $val, $alt_body);
                    }
                    
                    $mail->setSubject($subject);
                    $mail->setBody($body);
                    $mail->setAltbody($alt_body);
                    
                    $state = 0;
                }
                else {
                    $state = 1;
                }
            }
            
            $this->redirectExit($this->url('register.done', [
                'state' => $state
            ]));
            return;
        }
        
        if (empty($data)) {
            $data = $this->model->getRegister();
        }
        
        $form = $this->getFormDesigner($data);
        $form->setName('core-register-user');
        
        $group = $form->addGroup();
        
        $username = $group->addControl('Text', 'username');
        
        $text = $this->text('register.form.username');
        $username->setPlaceholder($text);
        $username->noLabel();
        
        $password = $group->addControl('Password', 'password');
        
        $text = $this->text('register.form.password');
        $password->setPlaceholder($text);
        $password->noLabel();
        
        if ($this->cfg('security.register.use_compare_password')) {
            $password_compare = $group->addControl('Password', 'password_compare');
            
            $text = $this->text('register.form.compare');
            $password_compare->setPlaceholder($text);
            $password_compare->noLabel();
        }
        
        $btn_group_just = $group->addGroup();
        $btn_group_just->addCss('btn-group btn-group-sm btn-group-justified');
        
        $btn_group = $btn_group_just->addGroup();
        $btn_group->addCss('btn-group');
        
        $control = $btn_group->addControl('Submit');
        
        $icon = $this->getHtmlObject('Elements\Icon');
        $icon->useIcon('key');
        
        $control->setInner($icon->build() . ' ' . $this->text('register.form.button'));
        
        $this->setVar([
            'headline' => $this->text('register.form.headline'),
            'form' => $form,
            'state' => 0
        ]);
    }

    public function Done($state)
    {
        $this->setVar([
            'headline' => $this->text('register.activation.' . $state . '.headline'),
            'text' => $this->text('register.activation.' . $state . '.text')
        ]);
    }

    public function Activate($key)
    {
        // $key = $this->router->getParam('key');
        $id_user = $this->di->get('core.sec.users')->activateUser($key);
        
        // Redirect to RegisterDone on successfull activation
        if ($id_user) {
            $this->redirectExit($this->url('register.done', [
                'state' => 1
            ]));
            return;
        }
        
        $this->setVar([
            'headline' => $this->text($key)
        ]);
    }

    Public function Deny($key)
    {}
}

