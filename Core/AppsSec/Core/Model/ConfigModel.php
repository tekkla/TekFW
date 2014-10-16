<?php
namespace Core\AppsSec\Core\Model;

use Core\Lib\Amvc\Model;
use Core\Lib\Data\Data;

/**
 * Description
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package AppSec Admin
 * @subpackage Model/Config
 * @license MIT
 * @copyright 2014 by author
 */
final class ConfigModel extends Model
{

	protected $tbl = 'config';

	protected $alias = 'cfg';

	protected $pk = 'id_config';

	public function loadConfig()
	{
		return $this->read('config');
	}

	public function loadByApp($app_name)
	{
		// get config structure from app
		$app = $this->di['core.amvc.creator']->create($app_name);
		$cfg_def = $app->getConfigDefinition();

		if ($cfg_def) {

			$this->data = new Data();

			$cfg_def_keys = array_keys($cfg_def);

			// set config values to app config structure
			foreach ($cfg_def_keys as $key) {

				if ($this->cfg->exists($app_name, $key)) {
					$val = $this->cfg->get($app_name, $key);
				}
				else{
					$val = isset($cfg_def[$key]['default']) && $cfg_def[$key]['default'] !== '' ? $cfg_def[$key]['default'] : '';
				}

				$this->data->{$key} = $val;
			}

			return $this->data;
		}

		// return structure
		return false;
	}

	public function loadAsTree()
	{
		$apps = $this->setField('app')
			->isDistinct()
			->setOrder('app')
			->read('keysonly');

		$out = [];

		foreach ($apps as $app) {
			$out[$app] = $this->loadByApp($app);
		}

		return $out;
	}

	/**
	 * Rewrites the config in DB with the config definition of the app.
	 * New configs will be saved to db and obsolete entries will be removed from db
	 *
	 * @param string $app
	 */
	public function rewriteConfig($app_name)
	{
		// app names are in config db always with underscores and not camelized
		$app_name = $this->uncamelizeString($app_name);

		// get config definition from app
		$app_default_config = $this->di['core.amvc.creator']->create($app_name)->getConfigDefinition();

		// load app config from db
		$this->setFilter('app={string:app}', [
			'app' => $app_name
		]);
		$current_config = $this->read('*');

		// compare current config with default config
		foreach ($current_config as $cfg) {

			// is this config still in the default config of this app?
			if (! isset($app_default_config[$cfgcfg])) {

				// no, then remove it
				$this->setFilter('app={string:app} AND cfg={string:cfg}');
				$this->setParameter([
					'app' => $app_name,
					'cfg' => $cfg->cfg
				]);
				$this->delete();

				echo 'Delete: ' . $app_name . ' => ' . $cfg->cfg . '<b';
			}

			// this is a valif config, so remove it from the list of possible new configs
			unset($app_default_config[$cfgcfg]);
		}

		// reset set model filter
		$this->resetFilter();

		// insert the remaining new configs from default config app
		foreach ($app_default_config as $cfg_key => $cfg) {
			$data = new \stdClass();
			$data->app = $app_name;
			$data->cfg = $cfg_key;
			$data->val = $cfg[1];

			$this->addData($data);
		}

		// insert new configs
		$this->save();
	}

	public function saveConfig($data)
	{
		// Store the appname this config is for
		$app_name = $data->app;

		// Get config definition from app
		$app_config_def = $this->di['core.amvc.creator']->create($app_name)->getConfigDefinition();


		// Remove appname and btn values from data
		unset($data->app, $data->btn_submit);

		// From here the app name is needed as underscored string
		$app_name = $this->uncamelizeString($app_name);

		// Set data to model so the validator has work to do
		$this->data = $data;

		// Get the keys from send data as fieldnames to check on validator
		$data_fld_list = array_keys(get_object_vars($data));

		// Add possible validatipons rules
		foreach ($data_fld_list as $fld) {
			// try to get validation rules from config definition
			if (isset($app_config_def[$fld]['validate'])) {
				$this->addValidationRule($fld, $app_config_def[$fld]['validate']);
			}
		}

		// Validate!
		$this->validate();

		// Was walidation a success or did we get some errors?
		if ($this->hasErrors()) {
			return;
		}

		// No errors found. Delete the current app config from db
		$this->setFilter('app={string:app}', [
			'app' => $app_name == 'admin' ? 'core' : $app_name
		]);

		$this->delete();

		// The real config model is needed. ;)
		$config_model = $this->getModel($this->name);

		// and now save the config values
		foreach ($data as $fld => $val) {
			if ($val === '' && (! isset($app_config_def[$fld]['default']) || (isset($app_config_def[$fld]['default']) && $app_config_def[$fld]['default'] === ''))) {
				continue;
			}

			$cfg_data = new Data();

			$cfg_data->app = $app_name;
			$cfg_data->cfg = $fld;
			$cfg_data->val = $val;

			// Save config without further validation
			$config_model->setData($cfg_data);

			$this->save(false);
		}
	}
}
