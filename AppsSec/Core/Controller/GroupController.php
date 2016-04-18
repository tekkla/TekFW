<?php
namespace AppsSec\Core\Controller;

use Core\Amvc\Controller;
use Core\Html\Form\Input;

/**
 * GroupController.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class GroupController extends Controller
{

    protected $access = [
        '*' => 'admin'
    ];

    /**
     *
     * @var \AppsSec\Core\Model\GroupModel
     */
    public $model;

    public function Index()
    {
        $data = $this->model->getGroups();
        
        $this->setVar([
            'headline' => $this->text('group.list'),
            'id_group' => $this->text('group.field.id_group'),
            'group' => $this->text('group.singular'),
            'members' => $this->text('group.members'),
            'grouplist' => $data
        ]);
        
        $this->setAjaxTarget('#core-admin');
    }

    public function Detail($id)
    {
        $this->setVar([
            'title' => $this->text('group.field.title'),
            'display_name' => $this->text('group.field.display_name'),
            'description' => $this->text('group.field.description'),
            'group' => $this->model->getGroup($id),
            'url' => $this->url('edit', [
                'controller' => 'Group',
                'id' => $id
            ]),
            'permissions' => $this->app->getController('GroupPermission')
                ->run('Index', [
                'id' => $id
            ])
        ]);
    }

    public function Edit($id = null)
    {
        $data = $this->http->post->get();
        
        if ($data) {
            
            $this->model->save($data);
            // $this->redirectExit($this->url($this->router->getCurrentRoute(), ['id' => $id]));
        }
        else {
            $data = $this->model->getGroup($id);
        }
        
        $fd = $this->getFormDesigner();
        
        $group = $fd->addGroup();
        $group->mapData($data);
        
        // Add hidden field with project id on edits
        if (! empty($id)) {
            $group->addControl('hidden', 'id_group');
        }
        
        $controls = [
            'title' => 'text',
            'display_name' => 'text',
            'description' => 'textarea'
        ];
        
        foreach ($controls as $name => $type) {
            
            $control = $group->addControl($type, $name);
            
            if (method_exists($control, 'setLabel')) {
                
                $text = $this->text('group.field.' . str_replace('id_', '', $name));
                $control->setLabel($text);
                
                if ($control instanceof Input) {
                    $control->setPlaceholder($text);
                }
            }
        }
        
        /* @var $editbox \Core\Html\Controls\Editbox */
        $editbox = $this->html->create('Controls\Editbox');
        $editbox->setForm($fd);
        
        // Editbox caption and texts
        $editbox->setCaption($this->text('group.action.edit.text'));
        $editbox->setSaveText($this->text('action.save.text', 'Core'));
        $editbox->setCancelText($this->text('action.cancel.text', 'Core'));
        
        // Publish to view
        $this->setVar([
            'form' => $editbox,
            'project_headline' => $this->text('group.action.edit.text')
        ]);
    }
}

