<?php
namespace Core\AppsSec\Core\Controller;

use Core\Lib\Amvc\Controller;
use Core\Lib\Errors\Exceptions\RuntimeException;
use Core\AppsSec\Core\Model\ConfigModel;
use Core\Lib\Html\FormDesigner\FormGroup;
use Core\Lib\Data\Container\Container;
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

        $this->setAjaxTarget('#core-admin-config');
    }

    public function ConfigGroup($app_name, $group_name)
    {
        // Camelize app name becaus this parameter comes uncamelized from request handler
        $app_name = $this->stringCamelize($app_name);

        // check permission
        if (! $this->checkAccess($app_name . '_config')) {
            Throw new SecurityException('No accessrights');
        }

        $data = $this->http->post->getArray();

        // save process
        if ($data) {

            $data = $this->model->saveConfig($data);

            if (! $data->hasErrors()) {
                unset($data);

                // Reload config
                $this->di->get('core.cfg')->load();
            }
        }

        // Load the app's config data
        if (empty($data)) {
            $data = $this->model->loadByApp($app_name);
        }

        // Use form designer
        $form = $this->getFormDesigner($data);
        $form->isAjax();
        $form->isHorizontal('sm', 3);
        $form->setId('core-config-' . strtolower($app_name) . '-' . $group_name);
        $form->setName('core-config-' . strtolower($app_name) . '-' . $group_name);

        // Set forms action route
        $form->setActionRoute('config_group', array(
            'app_name' => $app_name,
            'group_name' => $group_name
        ));

        $group = $form->addGroup();

        // Add hidden app control
        $group->addControl('hidden', 'app_name')->setValue($app_name);

        $group = $form->addGroup();

        $config = $this->di->get('core.amvc.creator')
            ->getAppInstance($app_name)
            ->getConfig();

        $this->createControls($app_name, $config['raw'][$group_name], $group_name, $group, $data, 1);

        $group = $form->addGroup();

        $control = $group->addControl('Submit');
        $control->setUnbound();
        $control->setInner('<i class="fa fa-' . $this->text('actions.save.icon') . '"></i> ' . $this->text('actions.save.text'));
        $control->addCss([
            'btn-sm',
            'btn-block'
        ]);

        $this->setVar([
            'headline' => $this->text('config.' . $group_name . '.head'),
            'app_name' => $app_name,
            'group_name' => $group_name,
            'form' => $form,
            'error' => $data->hasErrors()
        ]);

        $this->setAjaxTarget('#config-' . $group_name);
    }

    private function createControls($app_name, $config, $prefix, FormGroup $group, Container $data, $level = 0, $glue = '.')
    {
        $level ++;

        foreach ($config as $key => $value) {

            if (array_key_exists('name', $value)) {

                $name = $prefix . $glue . $value['name'];

                $settings = $this->di->get('core.amvc.creator')
                    ->getAppInstance($app_name)
                    ->getConfig()['flat'][$name];

                $this->createControl($group, $name, $app_name, $settings, $data);
            }
            else {

                $subgroup = $group->addGroup();
                $subgroup->addCss('bottom-buffer');

                $heading = $subgroup->addElement('Elements\Heading');
                $heading->setSize($level + 1);
                $heading->setInner($this->text('config' . $glue . $prefix . $glue . $key . '.head', $app_name));

                if ($level == 2) {
                    $heading->addCss([
                        'no-top-margin',
                        'text-uppercase'
                    ]);

                    $subgroup->addCss([
                        'well',
                        'well-sm'
                    ]);
                }

                $this->createControls($app_name, $value, $prefix . $glue . $key, $subgroup, $data, $level);
            }
        }
    }

    private function createControl(FormGroup $group, $name, $app_name, $settings, $data)
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

        $value = $data[$name];

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

                \FB::log($settings);

                if ($settings['translate']) {
                    $value = $this->text($value, $app_name);
                }

                if ($control_type == 'textarea') {
                    $control->setInner($value);
                }
                else {
                    $control->setValue($value);
                }

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
        $icon = $this->getHtmlObject('Elements\Icon');
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
