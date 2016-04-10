<?php
namespace AppsSec\Core\Controller;

use Core\Amvc\Controller;

/**
 * GroupPermissionController.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class GroupPermissionController extends Controller
{

    /**
     *
     * @var \AppsSec\Core\Model\GroupPermissionModel
     */
    public $model;

    public function Index($id)
    {
        $this->redirect('PermissionsByGroup', [
            'id' => $id
        ]);
    }

    public function PermissionsByGroup($id)
    {
        $this->setVar([
            'headline' => $this->text('permission.plural'),
            'permissions' => $this->model->loadPermissionsGroupedByApps([
                $id
            ])
        ]);
    }

    public function Detail($id)
    {
        $this->setVar([
            'headline' => $this->text('permission.plural'),
            'permissions' => $this->model->loadPermissions([
                $id
            ])
        ]);
    }

    public function Edit($id_parent, $id = null)
    {
        $data = $this->http->post->get()['core'];

        if ($data) {

            $this->model->save($data);

            if (!$this->model->hasErrors()) {
                $this->redirect('Detail', [
                    'id' => $data['id_group_permission']
                ]);
                return;
            }
        }

        if (! $data) {
            $data = $this->model->getEdit($id);
        }

        $fd = $this->getFormDesigner();
        $fd->isAjax();
        $fd->mapData($data);
        $fd->mapErrors($this->model->getErrors());

        $group = $fd->addGroup();

        // Add hidden field with customer id on edits
        if (! empty($id)) {
            $group->addControl('hidden', 'id_group_permission');
        }

        $controls = [
            'permission' => 'select',
            'notes' => 'textarea'
        ];

        foreach ($controls as $name => $type) {

            if ($name == 'options_heading') {
                $group->addHtml('<h4>' . $type . '</h4>');
                continue;
            }

            $text = $this->text('group_permission.field.' . $name);

            $control = $group->addControl($type, $name);
            $control->setLabel($text);

            if (method_exists($control, 'setPlaceholder')) {
                $control->setPlaceholder($text);
            }

            switch ($name) {
                case 'permission':

                    // Get all app permissions
                    $permissions = $this->security->permission->getPermissions();

                    foreach ($permissions as $app_name => $perms) {

                        foreach ($perms as $perm) {
                            $control->newOption($app_name . '.' . $perm, null, false, $app_name);
                        }
                    }

                    break;

                case 'notes':
                    $control->setRows(2);
                    break;
            }
        }

        /* @var $editbox \Core\Html\Controls\Editbox */
        $editbox = $this->html->create('Controls\Editbox');
        $editbox->setForm($fd);

        if (! empty($id)) {
            $caption = $this->text('group_permission.action.edit.text');
        }
        else {
            $caption = $this->text('group_permission.action.new.text');
        }

        $editbox->setCaption($caption);
        $editbox->setCancelAction($this->url('byid', [
            'controller' => 'Group',
            'action' => 'Detail',
            'id' => $id_parent
        ]));
        $editbox->setSaveText($this->text('action.save.text', 'Core'));
        $editbox->setCancelText($this->text('action.cancel.text', 'Core'));

        $this->setVar('form', $editbox);
    }
}

