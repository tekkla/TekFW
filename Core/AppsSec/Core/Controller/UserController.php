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
        $this->redirect('Userlist');
    }

    public function Userlist()
    {
        $this->setVar([
            'userlist' => $this->model->getList('display_name', '%', 100, [
                [
                    function ($data) {
                        $data['link'] = $this->url('edit', [
                            'controller' => 'User',
                            'id' => $data['id_user']
                        ]);
                        return $data;
                    }
                ]
            ]),
            'links' => [
                'new' => [
                    'text' => $this->text('user.action.new.text'),
                    'url' => $this->url('edit', [
                        'controller' => 'user'
                    ])
                ]
            ],
            'text' => [
                'headline' => $this->text('user.list'),
                'username' => $this->text('user.field.username'),
                'display_name' => $this->text('user.field.display_name')
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

            if (! $data->hasErrors()) {
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

        $fd->addData($data);

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
        $heading->setInner($this->text('user.field.groups'));

        $groups = $this->security->group->getGroups();

        /* @var $control \Core\Lib\Html\Controls\Optiongroup */
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

        /* @var $editbox \Core\Lib\Html\Controls\Editbox */
        $editbox = $this->html->create('Controls\Editbox');
        $editbox->setForm($fd);

        // Editbox caption
        $editbox->setCaption($this->text('user.action.edit.text'));

        // Cancel action only when requested
        $editbox->setCancelAction($this->url('byid', [
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
}

