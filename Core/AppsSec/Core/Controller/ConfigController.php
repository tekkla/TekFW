<?php
namespace Core\AppsSec\Core\Controller;

use Core\Lib\Amvc\Controller;
use Core\Lib\Amvc\App;
use Core\Lib\Data\Data;

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
	 * @param unknown $app_name
	 * @throws \RuntimeException
	 * @return void|boolean
	 */
	public function Config($app_name)
	{
		// Camelize app name becaus this parameter comes uncamelized from request handler
		$app_name = $this->camelizeString($app_name);

		// check permission
		if (! $this->checkAccess($app_name . '_config')) {
			Throw new \RuntimeException('No accessrights');
		}

		$post = $this->post->get();

		// save process
		if ($post) {
			$this->model->saveConfig($post);

			if ($this->model->hasNoErrors()) {
				$this->messagesuccess($this->txt('config_saved'));
				$redir_url = $this->router->url($this->router->getCurrentRoute(), array(
					'app_name' => $this->uncamelizeString($app_name)
				));
				$this->redirectExit($redir_url);
				return;
			}
		}

		// config headarea
		$this->setVar(array(
			'app_name' => $this->txt('name', $app_name),
			'icon' => $this->html->create('Elements\Icon')->useIcon('cog')
		));

		// Load the app's config data
		if ($this->model->hasNoData()) {
			$this->model->loadByApp($app_name);
		}

		// Use form designer
		$form = $this->getFormDesigner();

		// Set forms action route
		$form->setActionRoute($this->router->getCurrentRoute(), array(
			'app_name' => $this->uncamelizeString($app_name)
		));

		// The app name is always needed
		$form->createElement('hidden', 'app')->setValue($app_name);

		// storage for active group
		$groupname = '';

		// Get the config definition from app
		$app = $this->di['core.amvc.creator']->create($app_name);
		$app_cfg = $app->getConfig();

		// controls for each config key will be created as a loop
		foreach ($this->model->data as $cfg_key => $cfg_value) {

			if ($cfg_key == 'app' || $cfg_key == 'btn_submit') {
				continue;
			}

			// Get the config definition of this config key
			// $cfg_def = Lib::toObject($app_cfg[$cfg_key]);
			$cfg_def = new Data($app_cfg[$cfg_key]);

			// add a group header if the controls group is
			// different the one stored as active group
			if ($cfg_def->group !== $groupname) {

				$group = $form->openGroup($cfg_def->group);
				$group->setHeading($this->txt($this->uncamelizeString('app_' . ($app_name == 'admin' ? 'web' : $app_name) . '_cfg_group_' . $cfg_def->group)), 3);
				$group->addCss('app-admin-config-group');
				$group->noRow();

				// Set this group as active group
				$groupname = $cfg_def->group;
			}

			// Is this a control with more settings or only the controltype
			$control_type = is_object($cfg_def->control) ? $cfg_def->control{0} : $cfg_def->control;

			// Create control object
			$control = $form->createElement($control_type, $cfg_key);

			// Are there attributes to add?
			if (is_object($cfg_def->control) && isset($cfg_def->control{1}) && is_object($cfg_def->control{1})) {

				// Add all attributes to the control
				foreach ($cfg_def->control{1} as $attr => $val) {
					$control->addAttribute($attr, $val);
				}
			}

			// Create controls
			switch ($control_type) {
				case 'textarea':
				case 'text':
					if (isset($cfg_def->default) && ! isset($cfg_deftranslate)) {
						$cfg_def->default = $this->txt($app_name . '_' . $cfg_def->default);
					}
					break;

				// Create datasource driven controls
				case 'optiongroup':
				case 'select':
				case 'multiselect':

					if (! isset($cfg_def->data) || isset($cfg_def->data) && ! is_object($cfg_def->data)) {
						Throw new \RuntimeException('No or not correct set data definition.');
					}

					// Load optiongroup datasource type
					switch ($cfg_def->data->{0}) {
						// Type: model
						case 'model':
							list ($model_app, $model_name, $model_action) = explode('::', $cfg_def->data->{1});
							$datasource = App::create($model_app)->getModel($model_name);
							break;

						// Type: array
						case 'array':
							$datasource = $cfg_def->data->{1};
							break;

						// Datasource has to be of type array or model. All other will result in an exception
						default:
							Throw new \RuntimeException('Wrong or none datasource set for control "' . $cfg_key . '" of type "' . $cfg_def->control . '"');
					}

					// if no bound column number is set, set default to column 0
					if (! isset($cfg_def->data->{2})) {
						$cfg_def->data->{2} = 0;
					}

					// Create the list of options
					foreach ($datasource as $key => $val) {

						$option_value = $cfg_def->data->{2} == 0 ? $key : $val;

						$option = $control->createOption();
						$option->setInner($val);
						$option->setValue($option_value);

						if (is_object($cfg_value)) {
							foreach ($cfg_value as $k => $v) {
								if (($control_type == 'multiselect' && $v == html_entity_decode($option_value)) || ($control_type == 'optiongroup' && ($cfg_def->data->{2} == 0 && $k == $option_value) || ($cfg_def->data->{2} == 1 && $v == $option_value))) {
									$option->isSelected(1);
									unset($cfg_value{$k});
									continue;
								}
							}
						}
						else {

							// this is for simple select control
							if (($cfg_def->data->{2} == 0 && $key === $cfg_value) || ($cfg_def->data->{2} == 1 && $val == $cfg_value)) {
								$option->isSelected(1);
							}
						}
					}

					break;

				case 'switch':
					if ($cfg_value == 1) {
						$control->switchOn();
					}
					break;

				default:
					if (! $control->checkAttribute('size')) {
						$control->setSize(55);
					}

					break;
			}

			$txt = $this->uncamelizeString('cfg_' . $cfg_key);
			$app = $app_name == 'admin' ? 'web' : $app_name;

			$control->setLabel($this->txt($txt, $app));
			$control->setDescription($this->txt($txt . '_desc', $app));
		}

		$this->setVar('form', $form);

		// Add linktreee
		$this->content->breadcrumbs->createItem('TekFW Admincenter', $this->router->url('core_admin'));

		$this->content->breadcrumbs->createActiveItem($this->txt('name', $app_name));
	}

	public function Reconfigure($app_name)
	{
		$this->model->rewriteConfig($app_name);
	}
}
