<?php
namespace AppsSec\Core\Controller;

use Core\Amvc\Controller;
use AppsSec\Core\Model\ConfigModel;
use Core\Html\FormDesigner\FormGroup;
use Core\Security\SecurityException;
use Core\Amvc\ControllerException;
use Core\Error\CoreException;

/**
 * ConfigController.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class ConfigController extends Controller
{

    protected $access = [
        '*' => [
            'admin'
        ]
    ];

    /**
     *
     * @var ConfigModel
     */
    public $model;

    public function Config($app_name)
    {
        $app_name = $this->stringCamelize($app_name);
        
        $groups = $this->model->getConfigGroups($app_name);
        
        foreach ($groups as $group_name) {
            
            $forms[$group_name] = $this->app->getController()->run('Group', [
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

    public function Group($app_name, $group_name)
    {
        // Camelize app name becaus this parameter comes uncamelized from request handler
        $app_name = $this->stringCamelize($app_name);
        
        // check permission
        if (! $this->checkAccess('config', false, $app_name)) {
            Throw new SecurityException('No accessrights');
        }
        
        $data = $this->http->post->get();
        
        // save process
        if ($data) {
            
            $this->model->saveConfig($data);
            
            if (! $this->model->hasErrors()) {
                
                // Reload config and force a cache refresh!
                $this->di->get('core.cfg')->load(true);
                unset($data);
            }
        }
        
        if (empty($data)) {
            $data = $this->model->getData($app_name, $group_name);
        }
        
        // Use form designer
        $fd = $this->getFormDesigner('core-admin-config-' . $app_name . '-' . $group_name);
        $fd->mapErrors($this->model->getErrors());
        $fd->mapData($data);
        $fd->isHorizontal('sm', 4);
        $fd->isAjax();
        
        // Set forms action route
        $fd->html->setAction($this->url('config.group', [
            'app_name' => $app_name,
            'group_name' => $group_name
        ]));
        
        $group = $fd->addGroup();
        
        // Add hidden app control
        $group->addControl('hidden', 'app')->setValue($app_name);
        
        $group = $fd->addGroup();
        $group->setName($group_name);
        
        $this->createGroups($app_name, $this->di->get('core.cfg')->definitions[$app_name][$group_name], $group, 0, [
            $group_name
        ]);
        
        $group = $fd->addGroup();
        
        $control = $group->addControl('Submit');
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

    private function createGroups($app_name, $config, FormGroup $group, $level = 0, $names = [])
    {
        $level ++;
        
        foreach ($config as $key => $value) {
            
            if (array_key_exists('name', $value) && is_string($value['name'])) {
                
                $value['groupnames'] = $names;
                
                $this->createControl($app_name, $group, $value);
            }
            else {
                
                $subgroup = $group->addGroup();
                $subgroup->setName($key);
                
                $subgroup_names = $names;
                $subgroup_names[] = $key;
                
                $subgroup->html->addCss('bottom-buffer');
                
                if ($level == 1) {
                    
                    $heading = $subgroup->addElement('Elements\Heading');
                    $heading->setSize($level + 3);
                    
                    $heading->setInner($this->text('config.' . implode('.', $subgroup_names) . '.head', $app_name));
                    
                    $heading->addCss([
                        'no-top-margin',
                        'text-uppercase'
                    ]);
                    
                    $subgroup->html->addCss([
                        'well',
                        'well-sm'
                    ]);
                }
                
                $this->createGroups($app_name, $value, $subgroup, $level, $subgroup_names);
            }
        }
    }

    private function createControl($app_name, FormGroup $group, array $settings)
    {
        if ($settings['name'] == 'app.name') {
            return;
        }
        
        // Check for missing settings and extend settings if needed
        $this->model->checkDefinitionFields($settings);
        
        // Get value for this control from stored config by using flattened key
        $flat_name = implode('.', $settings['groupnames']) . '.' . $settings['name'];
        
        $cfg = $this->di->get('core.cfg');
        
        if (! empty($cfg->data[$app_name][$flat_name])) {
            $settings['value'] = $cfg->data[$app_name][$flat_name];
        }
        
        // Is this a control with more settings or only the controltype
        $control_type = $settings['control'];
        
        // We need the controls type even when the control is data driven
        if (is_array($control_type)) {
            $control_type = $control_type[0];
        }
        
        // Create control object
        $control = $group->addControl($control_type, $settings['name']);
        
        // Are there attributes to add?
        if (is_array($settings['control']) && isset($settings['control'][1]) && is_array($settings['control'][1])) {
            
            // Add all attributes to the control
            foreach ($settings['control'][1] as $attr => $val) {
                $control->addAttribute($attr, $val);
            }
        }
        
        // Create controls
        switch ($control_type) {
            
            // Create datasource driven controls
            case 'optiongroup':
            case 'select':
            case 'multiselect':
                
                if (! $settings['data']) {
                    Throw new CoreException('No or not correct set data definition.');
                }
                
                // Load optiongroup datasource type
                switch ($settings['data']['type']) {
                    
                    // DataType: model
                    case 'model':
                        
                        // Check settings!
                        $check = [
                            'app',
                            'model',
                            'action'
                        ];
                        
                        foreach ($check as $ama) {
                            if (empty($settings['data']['source'][$ama])) {
                                Throw new ControllerException(sprintf('Data driven controls with model as datasource needs settings about %s', $ama));
                            }
                        }
                        
                        /* @var $creator \Core\Amvc\Creator */
                        $creator = $this->di->get('core.amvc.creator');
                        $app = $creator->getAppInstance($settings['data']['source']['app']);
                        $model = $app->getModel($settings['data']['source']['model']);
                        $action = $settings['data']['source']['action'];
                        
                        $model->checkMethodExists($action);
                        
                        $params = ! empty($settings['data']['source']['params']) ? $settings['data']['source']['params'] : [];
                        
                        $datasource = call_user_func_array([
                            $model,
                            $action
                        ], $params);
                        
                        break;
                    
                    // DataType: array
                    case 'array':
                        $datasource = $settings['data']['source'];
                        break;
                    
                    // Datasource has to be of type array or model. All other will result in an exception
                    default:
                        Throw new CoreException(sprintf('Wrong or none datasource set for control "%s" of type "%s"', $settings['name'], $control_type));
                }
                
                // Add 'please select' option
                if (empty($settings['value'])) {
                    $option = $control->createOption();
                    $option->setInner($this->text('please.select'));
                    $option->setValue('');
                    $option->isDisabled(1);
                    $option->isHidden(1);
                    $option->isSelected(1);
                }
                
                // Create the list of options
                foreach ($datasource as $ds_key => $ds_val) {
                    
                    $option_value = $settings['data']['index'] == 0 ? $ds_key : $ds_val;
                    
                    $option = $control->createOption();
                    $option->setInner($ds_val);
                    $option->setValue($option_value);
                    
                    if (is_array($settings['value'])) {
                        foreach ($settings['value'] as $k => $v) {
                            if (($control_type == 'multiselect' && $v == html_entity_decode($option_value)) || ($control_type == 'optiongroup' && ($settings['data']['index'] == 0 && $k == $option_value) || ($settings['data']['index'] == 1 && $v == $option_value))) {
                                $option->isSelected(1);
                                continue;
                            }
                        }
                    }
                    else {
                        
                        // this is for simple select control
                        if (($settings['data']['index'] == 0 && $ds_key === $settings['value']) || ($settings['data']['index'] == 1 && $ds_val == $settings['value'])) {
                            $option->isSelected(1);
                        }
                    }
                }
                
                break;
            
            case 'switch':
                
                if ($settings['value'] == 1) {
                    $control->switchOn();
                }
                break;
            
            default:
                
                if (! empty($settings['translate'])) {
                    $settings['value'] = $this->text($settings['value'], $app_name);
                }
                
                $control->setValue($settings['value']);
                
                break;
        }
        
        /* @var $icon \Core\Html\Elements\Icon */
        $icon = $this->html->create('Elements\Icon');
        $icon->setIcon('question-circle');
        $icon->addData([
            'toggle' => 'popover',
            'trigger' => 'click',
            'content' => $this->text('config.' . $flat_name . '.desc')
        ]);
        
        $control->setLabel($this->text('config.' . $flat_name . '.label') . ' ' . $icon->build());
    }
}
