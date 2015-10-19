<?php
namespace Core\AppsSec\Core\Controller;

use Core\Lib\Amvc\Controller;
use Core\Lib\Errors\Exceptions\SecurityException;
use Core\Lib\Errors\Exceptions\RuntimeException;

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
     * @param string $app_name
     *
     * @throws SecurityException
     * @throws RuntimeException
     *
     * @return void|boolean
     */
    public function Config($app_name)
    {
        // Camelize app name becaus this parameter comes uncamelized from request handler
        $app_name = $this->camelizeString($app_name);

        // check permission
        if (! $this->checkAccess($app_name . '_config')) {
            Throw new SecurityException('No accessrights');
        }

        $data = $this->post->get();

        // save process
        if ($data) {

            $this->model->saveConfig($data);

            if (! $data->hasErrors()) {
                $this->content->msg->success($this->txt('config_saved'));
                $redir_url = $this->url($this->router->getCurrentRoute(), [
                    'app_name' => $this->uncamelizeString($app_name)
                ]);

                $this->redirectExit($redir_url);
                return;
            }
        }

        // config headarea
        $this->setVar(array(
            'app_name' => $this->txt('name', $app_name),
            'icon' => $this->html->create('Elements\Icon')
                ->useIcon('cog')
        ));

        // Load the app's config data
        if (empty($data)) {
            $data = $this->model->loadByApp($app_name);
        }

        // Use form designer
        $form = $this->getFormDesigner($data);

        // Set forms action route
        $form->setActionRoute($this->router->getCurrentRoute(), array(
            'app_name' => $this->uncamelizeString($app_name)
        ));

        $group = $form->addGroup();

        // Add hidden app control
        $group->addControl('hidden', 'app_name')->setValue($app_name);

        // storage for active group
        $groupname = '';

        // App creator
        $creator = $this->di->get('core.amvc.creator');

        // Get the config definition from app
        $app = $creator->getAppInstance($app_name);
        $app_cfg = $app->getConfig();

        // controls for each config key will be created as a loop
        foreach ($data as $key => $fld) {

            if ($key == 'app_name') {
                continue;
            }

            $cfg = $app_cfg[$key];

            // add a group header if the controls group is
            // different the one stored as active group
            if ($cfg['group'] !== $groupname) {

                $group = $form->addGroup();

                $group->addElement('Elements\Heading', [
                    'setInner' => $this->txt($this->uncamelizeString('cfg_group_' . $cfg['group'], $app_name)),
                    'setSize' => 4
                ]);

                // Set this group as active group
                $groupname = $cfg['group'];
            }

            // Is this a control with more settings or only the controltype
            if (! isset($cfg['control'])) {
                $cfg['control'] = 'text';
            }

            $control_type = is_array($cfg['control']) ? $cfg['control'][0] : $cfg['control'];

            // Create control object
            $control = $group->addControl($control_type, $key);

            // Are there attributes to add?
            if (is_array($cfg['control']) && isset($cfg['control'][1]) && is_array($cfg['control'][1])) {

                // Add all attributes to the control
                foreach ($cfg['control'][1] as $attr => $val) {
                    $control->addAttribute($attr, $val);
                }
            }

            // Create controls
            switch ($control_type) {
                case 'textarea':
                case 'text':

                    if (isset($cfg['translate'])) {
                        $cfg['value'] = $this->txt($app_name . '_' . $fld->getValue());
                    }

                    if ($control_type == 'textarea') {
                        $control->setInner($fld->getValue());
                    }
                    else {
                        $control->setValue($fld->getValue());
                    }

                    break;

                // Create datasource driven controls
                case 'optiongroup':
                case 'select':
                case 'multiselect':

                    if (! isset($cfg['data']) || isset($cfg['data']) && ! is_array($cfg['data'])) {
                        Throw new RuntimeException('No or not correct set data definition.');
                    }

                    // Load optiongroup datasource type
                    switch ($cfg['data'][0]) {

                        // DataType: model
                        case 'model':
                            list ($model_app, $model_name, $model_action) = explode('::', $cfg['data'][1]);
                            $datasource = $creator->getAppInstance($model_app)->getModel($model_name);
                            break;

                        // DataType: array
                        case 'array':
                            $datasource = $cfg['data'][1];
                            break;

                        // Datasource has to be of type array or model. All other will result in an exception
                        default:
                            Throw new RuntimeException('Wrong or none datasource set for control "' . $key . '" of type "' . $cfg['control'] . '"');
                    }

                    // if no bound column number is set, set default to column 0
                    if (! isset($cfg['data'][2])) {
                        $cfg['data'][2] = 0;
                    }

                    // Create the list of options
                    foreach ($datasource as $ds_key => $ds_val) {

                        $option_value = $cfg['data'][2] == 0 ? $ds_key : $ds_val;

                        $option = $control->createOption();
                        $option->setInner($ds_val);
                        $option->setValue($option_value);

                        if (is_array($fld->getValue())) {
                            foreach ($fld->getValue() as $k => $v) {
                                if (($control_type == 'multiselect' && $v == html_entity_decode($option_value)) || ($control_type == 'optiongroup' && ($cfg['data'][2] == 0 && $k == $option_value) || ($cfg['data'][2] == 1 && $v == $option_value))) {
                                    $option->isSelected(1);
                                    continue;
                                }
                            }
                        }
                        else {

                            // this is for simple select control
                            if (($cfg['data'][2] == 0 && $ds_key === $fld->getValue()) || ($cfg['data'][2] == 1 && $ds_val == $fld->getValue())) {
                                $option->isSelected(1);
                            }
                        }
                    }

                    break;

                case 'switch':

                    if ($fld->getValue() == 1) {
                        $control->switchOn();
                    }
                    break;

                default:
                    if (! $control->checkAttribute('size')) {
                        $control->setSize(55);
                    }

                    $control->setValue($fld->getValue());

                    break;
            }

            $txt = $this->uncamelizeString('cfg_' . $key);
            $app = $app_name == 'admin' ? 'web' : $app_name;

            $control->setLabel($this->txt($txt, $app));
            $control->setDescription($this->txt($txt . '_desc', $app));
        }


        $control = $group->addControl('Submit');
        $group->addElement('Elements\Hr');

        $this->setVar('form', $form);

        // Add linktreee
        $this->content->breadcrumbs->createItem('Admin', $this->router->url('core_admin'));

        $this->content->breadcrumbs->createActiveItem($this->txt('name', $app_name));
    }

    public function Reconfigure($app_name)
    {
        $this->model->rewriteConfig($app_name);
    }
}
