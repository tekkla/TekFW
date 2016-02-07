<?php
namespace Core\AppsSec\Core\Controller;

use Core\Lib\Amvc\Controller;

class UserController extends Controller
{

    /**
     *
     * @var UserModel
     */
    public $model;

    public function Index()
    {
        $this->redirect('ListByLetter');
    }

    public function ListByLetter($letter = 'A')
    {
        $this->setVar([
            'userlist' => $this->model->getList(),
            'links' => [
                'new' => [
                    'text' => $this->txt('user_new'),
                    'url' => $this->url('edit', [
                        'controller' => 'user'
                    ])
                ]
            ],
            'headline' => $this->txt('userlist')
        ]);
        
        $this->setAjaxTarget('#content');
    }

    public function Edit($id)
    {
        $data = $this->http->post->get();
        
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
        
        /* @var $editbox \Core\Lib\Html\Controls\Editbox */
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
    }
}

?>