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
class UserController extends Controller
{

    public function Index()
    {
        $this->setVar([
            'selection' => $this->getController()->run('Selection'),
            'start' => $this->getController()->run('Start')
        ]);

        $this->setAjaxTarget('#content');
    }

    public function Start()
    {
        $this->setAjaxTarget('#main');
    }

    public function Selection()
    {
        $data = $this->model->getAlphabet();
        $this->setVar([
            'alphabet' => $data
        ]);

        $this->setAjaxTarget('#selection');
    }

    public function ListByLetter($letter)
    {
        $this->setVar([
            'data' => $this->model->getUserlistByLetter($letter),
            'link' => $this->url('service_edit', [
                'controller' => 'user'
            ]),
            'new' => $this->txt('user_new'),
            'customer' => $this->txt('username'),
        ]);

        $this->setAjaxTarget('#main');
    }

    public function Edit($id)
    {
        $data = $this->post->get();

        if ($data) {

            $this->model->save($data);

            if (! $data->hasErrors()) {
                $this->redirect('Detail', [
                    'id' => $id
                ]);
                return;
            }
        }

        if (! $data) {
            $data = $this->model->getEdit($id);
        }

        // Get FormDesigner object
        $form = $this->getFormDesigner($data);

        // Flag form to be ajax
        $form->isAjax();

        // Start new group for controls
        $group = $form->addGroup();

        // Add hidden field for invoice id
        $group->addControl('hidden', 'id_user');

        // Username
        $control = $group->addControl('text', 'username');

        // Password
        $control = $group->addControl('password', 'password');

        // Usergroups
        $control = $group->addControl('Optiongroup', 'groups');

        /* @var $editbox \Core\Lib\Content\Html\Controls\Editbox */
        $editbox = $this->getHtmlObject('Controls\Editbox');
        $editbox->setForm($form);

        // Editbox caption
        $editbox->setCaption($this->txt('invoice_edit'));

        // Cancel action only when requested
        $editbox->setCancelAction($this->url('detail', [
            'controller' => 'User',
            'id' => $id
        ]));

        // Publish to view
        $this->setVar([
            'form' => $editbox
        ]);

        // Ajax target definition
        $this->setAjaxTarget('#content');
    }
}

