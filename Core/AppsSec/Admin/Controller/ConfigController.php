<?php
namespace Core\AppsSec\Admin\Controller;

use Core\Lib\Amvc\Controller;
use Core\Lib\Error;
use Core\Lib\Amvc\App;
use Core\Lib\Url;
use Core\Lib\Txt;
use Core\Lib\Content\Html\Elements\Icon;
use Core\Lib\String;
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
	 * @param unknown $app_name
	 * @throws Error
	 * @return void|boolean
	 */
	public function Config($app_name)
	{
		// Camelize app name becaus this parameter comes uncamelized from request handler
		$app_name = String::camelize($app_name);
		
		// check permission
		if (!$this->checkAccess('perm_' . $app_name . '_config'))
		{
			Throw new Error('No accessrights');
			return false;
		}
		
		$post = $this->request->getPost();
		
		// save process
		if ($post)
		{
			$this->model->saveConfig($post);
			
			if ($this->model->hasNoErrors())
			{
				$this->messagesuccess($this->txt('config_saved'));
				$redir_url = $this->request->getRouteUrl($this->request->getCurrentRoute(), array(
					'app_name' => String::uncamelize($app_name)
				));
				redirectexit($redir_url);
				return;
			}
		}
		
		// config headarea
		$this->setVar(array(
			'app_name' => Txt::get('name', $app_name), 
			'icon' => Icon::factory('cog')
		));
		
		// Load the app's config data
		if ($this->model->hasNoData())
			$this->model->loadByApp($app_name);
			
			// Use form designer
		$form = ->getFormDesigner();
		
		// Set forms action route
		$form->setActionRoute($this->request->getCurrentRoute(), array(
			'app_name' => String::uncamelize($app_name)
		));
		
		// The app name is always needed
		$form->createElement('hidden', 'app')->setValue($app_name);
		
		// storage for active group
		$groupname = '';
		
		// Get the config definition from app
		$app_cfg = App::create($app_name)->getConfigDefinition();
		
		// controls for each config key will be created as a loop
		foreach ( $this->model->data as $cfg_key => $cfg_value )
		{
			if ($cfg_key == 'app' || $cfg_key == 'btn_submit')
				continue;
				
				// Get the config definition of this config key
				#$cfg_def = Lib::toObject($app_cfg[$cfg_key]);
			$cfg_def = new Data($app_cfg[$cfg_key]);
			
			// add a group header if the controls group is
			// different the one stored as active group
			if ($cfg_defgroup !== $groupname)
			{
				$group = $form->openGroup($cfg_defgroup);
				$groupsetHeading(Txt::get(String::uncamelize('app_' . ( $app_name == 'admin' ? 'web' : $app_name ) . '_cfg_group_' . $cfg_defgroup)), 3);
				$groupaddCss('app-admin-config-group');
				$groupnoRow();
				
				// Set this group as active group
				$groupname = $cfg_defgroup;
			}
			
			// Is this a control with more settings or only the controltype
			$control->_type = is_object($cfg_defcontrol) ? $cfg_defcontrol{0} : $cfg_defcontrol;
			
			// Create control object
			$control = $form->createElement($control->_type, $cfg_key);
			
			// Are there attributes to add?
			if (is_object($cfg_defcontrol) && isset($cfg_defcontrol{1}) && is_object($cfg_defcontrol{1}))
			{
				// Add all attributes to the control
				foreach ( $cfg_defcontrol{1} as $attr => $val )
					$control->addAttribute($attr, $val);
			}
			
			// Create controls
			switch ($control->_type)
			{
				case 'textarea' :
				case 'text' :
					if (isset($cfg_defdefault) && !isset($cfg_deftranslate))
						$cfg_defdefault = Txt::get($app_name . '_' . $cfg_defdefault);
					break;
				
				// Create datasource driven controls
				case 'optiongroup' :
				case 'select' :
				case 'multiselect' :
					
					if (!isset($cfg_defdata) || isset($cfg_defdata) && !is_object($cfg_defdata))
						Throw new Error('No or not correct set data definition.');
						
						// Load optiongroup datasource type
					switch ($cfg_defdata{0})
					{
						// Type: model
						case 'model' :
							list($model_app, $model_name, $model_action) = explode('::', $cfg_defdata{1});
							$datasource = App::create($model_app)->getModel($model_name){$model_action}();
							break;
						
						// Type: array
						case 'array' :
							$datasource = $cfg_defdata{1};
							break;
						
						// Datasource has to be of type array or model. All other will result in an exception
						default :
							Throw new Error('Wrong or none datasource set for control "' . $cfg_key . '" of type "' . $cfg_defcontrol . '"');
							break;
					}
					
					// if no bound column number is set, set default to column 0
					if (!isset($cfg_defdata{2}))
						$cfg_defdata{2} = 0;
						
						// Create the list of options
					foreach ( $datasource as $key => $val )
					{
						$option_value = $cfg_defdata{2} == 0 ? $key : $val;
						
						$option = $control->createOption();
						$option->setInner($val);
						$option->setValue($option_value);
						
						if (is_object($cfg_value))
						{
							foreach ( $cfg_value as $k => $v )
							{
								if (( $control->_type == 'multiselect' && $v == html_entity_decode($option_value) ) || ( $control->_type == 'optiongroup' && ( $cfg_defdata{2} == 0 && $k == $option_value ) || ( $cfg_defdata{2} == 1 && $v == $option_value ) ))
								{
									$option->isSelected(1);
									unset($cfg_value{$k});
									continue;
								}
							}
						}
						else
						{
							// this is for simple select control
							if (( $cfg_defdata{2} == 0 && $key === $cfg_value ) || ( $cfg_defdata{2} == 1 && $val == $cfg_value ))
								$option->isSelected(1);
						}
					}
					
					break;
				
				case 'switch' :
					if ($cfg_value == 1)
						$control->switchOn();
					break;
				
				default :
					if (!$control->checkAttribute('size'))
						$control->setSize(55);
					
					break;
			}
			
			$txt = String::uncamelize('cfg_' . $cfg_key);
			$app = $app_name == 'admin' ? 'web' : $app_name;
			
			$control->setLabel(Txt::get($txt, $app));
			$control->setDescription(Txt::get($txt . '_desc', $app));
		}
		
		$this->setVar('form', $form);
		
		// Add linktreee
		$this->addLinktree('TekFW Admincenter', Url::factory('admin_index'));
		
		$this->addLinktree(Txt::get('name', $app_name));
	}

	public function Reconfigure($app_name)
	{
		$this->model->rewriteConfig($app_name);
	}
}
