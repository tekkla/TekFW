<?php
namespace Core\AppsSec\Core\Controller;

use Core\Lib\Amvc\Controller;
use Core\Lib\Html\Form\Input;

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
     * @var \Core\AppsSec\Core\Model\GroupModel
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

    public function Edit($id = null)
    {
        $data = $this->http->post->get();

        if ($data) {}
        else {
            $data = $this->model->getGroup($id);
        }

        $form = $this->getFormDesigner($data);
        $form->isAjax();

        $group = $form->addGroup();

        // Add hidden field with project id on edits
        if (! empty($id)) {
            $group->addControl('hidden', 'id_group');
        }

        $controls = [
            'title' => 'text',
            'display_name' => 'text',
            'permissions' => 'optiongroup'
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

            switch ($name) {

                // Perissions optiongroup
                case 'permissions':

                    // Get all loaded apps instances
                    $apps = $this->di->get('core.amvc.creator')->getLoadedApps(false);

                    /* @var $app \Core\Lib\Amvc\App */
                    foreach ($apps as $app) {

                        $perms = $app->getPermissions();

                        if (! $perms) {
                            continue;
                        }

                        $app_name = $app->getName();

                        /* @var $control \Core\Lib\Html\Controls\Optiongroup */
                        $control->createHeading($app_name);

                        foreach ($perms as $perm) {

                            $option = $control->createOption($this->text('perm.' . $perm . '.text'), $perm);
                            $option->setDescription($this->text('perm.' . $perm . '.desc'));

                            if (array_key_exists($perm, $data['permissions'][$app_name])) {
                                $option->isSelected();
                            }
                        }
                    }
                    break;
            }
        }

        /* @var $editbox \Core\Lib\Html\Controls\Editbox */
        $editbox = $this->getHtmlObject('Controls\Editbox');
        $editbox->setForm($form);

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

