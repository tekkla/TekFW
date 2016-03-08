<?php
namespace Core\AppsSec\Core\Controller;

use Core\Lib\Amvc\Controller;
use Core\Lib\Errors\Exceptions\RuntimeException;
use Core\AppsSec\Core\Model\ConfigModel;
use Core\Lib\Html\FormDesigner\FormGroup;
use Core\Lib\Security\SecurityException;

/**
 * ConfigController.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class ConfigController extends Controller
{

    public $actions = array(
        '*' => array(
            'access' => 'admin',
            'tools' => 'Form'
        )
    );

    /**
     *
     * @var ConfigModel
     */
    public $model;

    /**
     *
     * @param string $app_name
     *
     * @throws SecurityException
     * @throws RuntimeException
     *
     * @return void|boolean
     */
    public function Config($app_name)
    {
        $app_name = $this->stringCamelize($app_name);

        $groups = $this->model->getConfigGroups($app_name);

        foreach ($groups as $group_name) {

            $forms[$group_name] = $this->getController()->run('ConfigGroup', [
                'app_name' => $app_name,
                'group_name' => $group_name
            ]);
        }

        $this->setVar([
            'headline' => $this->text('name', $app_name),
            'icon' => $this->html->create('Elements\Icon')
                ->useIcon('cog'),
            'groups' => $groups,
            'forms' => $forms
        ]);

        // Add linktreee
        $this->page->breadcrumbs->createItem('Admin', $this->url('admin'));
        $this->page->breadcrumbs->createActiveItem($this->text('name', $app_name));

        $this->setAjaxTarget('#core-admin');
    }

    public function ConfigGroup($app_name, $group_name)
    {
        // Camelize app name becaus this parameter comes uncamelized from request handler
        $app_name = $this->stringCamelize($app_name);

        // check permission
        if (! $this->checkAccess('config', false, $app_name)) {
            Throw new SecurityException('No accessrights');
        }

        $data = $this->http->post->get()['core'];

        // save process
        if ($data) {

            $this->model->saveConfig($data);

            if (!$this->model->hasErrors()) {
                // Reload config
                $this->di->get('core.cfg')->load();
                unset($data);
            }
        }

        // Load the app's config data
        if (empty($data)) {
            $data = $this->model->loadByApp($app_name);
        }

        // Use form designer
        $fd = $this->getFormDesigner('core-admin-config-' . $app_name . '-' . $group_name);
        $fd->mapErrors($this->model->getErrors());
        $fd->mapData($data);

        $fd->isAjax();
        $fd->isHorizontal('sm', 3);

        // Set forms action route
        $url = $this->url('config.group', [
            'app_name' => $app_name,
            'group_name' => $group_name
        ]);
        $fd->html->setAction($url);

        $group = $fd->addGroup();

        // Add hidden app control
        $group->addControl('hidden', 'app.name')->setValue($app_name);

        $group = $fd->addGroup();

        $config = $this->di->get('core.amvc.creator')
            ->getAppInstance($app_name)
            ->getConfig();

        $this->createControls($app_name, $config['raw'][$group_name], $group_name, $group, 1);

        $group = $fd->addGroup();

        $control = $group->addControl('Submit', '');
        $control->setUnbound();
        $control->setInner('<i class="fa fa-' . $this->text('action.save.icon') . '"></i> ' . $this->text('action.save.text'));
        $control->addCss([
            'btn-sm',
            'btn-block'
        ]);

        $this->setVar([
            'headline' => $this->text('config.' . $group_name . '.head'),
            'app_name' => $app_name,
            'group_name' => $group_name,
            'form' => $fd,
            'error' => $this->model->hasErrors()
        ]);

        $this->setAjaxTarget('#config-' . $group_name);
    }

    private function createControls($app_name, $config, $prefix, FormGroup $group, $level = 0, $glue = '.')
    {
        $level ++;

        foreach ($config as $key => $value) {

            if (is_int($key)) {

                $name = $prefix . $glue . $value['name'];

                $settings = $this->di->get('core.amvc.creator')
                    ->getAppInstance($app_name)
                    ->getConfig()['flat'][$name];

                $this->createControl($group, $name, $app_name, $settings);
            }
            else {

                $subgroup = $group->addGroup();

                $subgroup->html->addCss('bottom-buffer');

                $heading = $subgroup->addElement('Elements\Heading');
                $heading->setSize($level + 1);
                $heading->setInner($this->text('config' . $glue . $prefix . $glue . $key . '.head', $app_name));

                if ($level == 2) {
                    $heading->addCss([
                        'no-top-margin',
                        'text-uppercase'
                    ]);

                    $subgroup->html->addCss([
                        'well',
                        'well-sm'
                    ]);
                }

                $this->createControls($app_name, $value, $prefix . $glue . $key, $subgroup, $level);
            }
        }
    }

    private function createControl(FormGroup $group, $name, $app_name, $settings)
    {
        if ($name == 'app.name') {
            return;
        }

        // Is this a control with more settings or only the controltype
        $control_type = $settings['control'];

        // We need the controls type even when the control is data driven
        if (is_array($control_type)) {
            $control_type = $control_type[0];
        }

        // if there is no value to in formdesigner data, use the value provided by settings
        $value = $settings['value'];

        // Create control object
        $control = $group->addControl($control_type, $name);

        // Are there attributes to add?
        if (is_array($settings['control']) && isset($settings['control'][1]) && is_array($settings['control'][1])) {

            // Add all attributes to the control
            foreach ($settings['control'][1] as $attr => $val) {
                $control->addAttribute($attr, $val);
            }
        }

        // Create controls
        switch ($control_type) {
            case 'textarea':
            case 'text':

                if (! empty($settings['translate'])) {
                    $value = $this->text($value, $app_name);
                }

                $control->setValue($value);

                break;

            // Create datasource driven controls
            case 'optiongroup':
            case 'select':
            case 'multiselect':

                if (! $settings['data']) {
                    Throw new RuntimeException('No or not correct set data definition.');
                }

                // Load optiongroup datasource type
                switch ($settings['data'][0]) {

                    // DataType: model
                    case 'model':
                        list ($model_app, $model_name, $model_action) = explode('::', $settings['data'][1]);
                        $datasource = $this->di->get('core.amvc.creator')
                            ->getAppInstance($model_app)
                            ->getModel($model_name);
                        break;

                    // DataType: array
                    case 'array':
                        $datasource = $settings['data'][1];
                        break;

                    // Datasource has to be of type array or model. All other will result in an exception
                    default:
                        Throw new RuntimeException(sprintf('Wrong or none datasource set for control "%s" of type "%s"', $name, $control_type));
                }

                // if no bound column number is set, set default to column 0
                if (! isset($settings['data'][2])) {
                    $settings['data'][2] = 0;
                }

                // Create the list of options
                foreach ($datasource as $ds_key => $ds_val) {

                    $option_value = $settings['data'][2] == 0 ? $ds_key : $ds_val;

                    $option = $control->createOption();
                    $option->setInner($ds_val);
                    $option->setValue($option_value);

                    if (is_array($value)) {
                        foreach ($value as $k => $v) {
                            if (($control_type == 'multiselect' && $v == html_entity_decode($option_value)) || ($control_type == 'optiongroup' && ($settings['data'][2] == 0 && $k == $option_value) || ($settings['data'][2] == 1 && $v == $option_value))) {
                                $option->isSelected(1);
                                continue;
                            }
                        }
                    }
                    else {

                        // this is for simple select control
                        if (($settings['data'][2] == 0 && $ds_key === $value) || ($settings['data'][2] == 1 && $ds_val == $value)) {
                            $option->isSelected(1);
                        }
                    }
                }

                break;

            case 'switch':

                if ($value == 1) {
                    $control->switchOn();
                }
                break;

            default:
                if (! $control->checkAttribute('size')) {
                    $control->setSize(55);
                }

                $control->setValue($value);

                break;
        }

        /* @var $icon \Core\Lib\Html\Elements\Icon */
        $icon = $this->html->create('Elements\Icon');
        $icon->setIcon('question-circle');
        $icon->addData([
            'toggle' => 'popover',
            'trigger' => 'click',
            'content' => $this->text('config.' . $name . '.desc')
        ]);

        $control->setLabel($this->text('config.' . $name . '.label') . ' ' . $icon->build());
    }

    public function Reconfigure($app_name)
    {
        $this->model->rewriteConfig($app_name);
    }
}
