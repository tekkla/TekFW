<?php
namespace Core\AppsSec\Admin\Model;

use Core\Lib\Amvc\Model;
use Core\Lib\Amvc\App;
use Core\Lib\Cfg;
use Core\Lib\String;
use Core\Lib\Data\Data;

/**
 * Description
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
		$cfg_def = App::create($app_name)->getConfigDefinition();

		if ($cfg_def)
		{
			$this->data = new Data();

			$cfg_def_keys = array_keys($cfg_def);

			// set config values to app config structure
			foreach ( $cfg_def_keys as $key )
			{
				if (Cfg::exists($app_name, $key))
					$val = Cfg::Get($app_name, $key);
				else
					$val = isset($cfg_def[$key]['default']) && $cfg_def[$key]['default'] !== '' ? $cfg_def[$key]['default'] : '';

				$this->data->{$key} = $val;
			}

			return $this->data;
		}

		// return structure
		return false;
	}

	public function loadAsTree()
	{
		$apps = ->setField('app')isDistinct()->setOrder('app')read('keysonly');

		$out = [];

		foreach ( $apps as $app )
			$out[$app] = ->loadByApp($app);

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
		$app_name = String::uncamelize($app_name);

		// get config definition from app
		$app_default_config = App::create($app_name)Cfg;

		// load app config from db
		$this->setFilter('app={string:app}', array(
			'app' => $app_name
		));
		$current_config = ->read('*');

		// compare current config with default config
		foreach ( $current_config as $cfg )
		{
			// is this config still in the default config of this app?
			if (!isset($app_default_config[$cfgcfg]))
			{
				// no, then remove it
				$this->setFilter('app={string:app} AND cfg={string:cfg}');
				$this->setParameter(array(
					'app' => $app_name,
					'cfg' => $cfgcfg
				));
				$this->delete();

				echo 'Delete: ' . $app_name . ' => ' . $cfgcfg . '<b';
			}

			// this is a valif config, so remove it from the list of possible new configs
			unset($app_default_config[$cfgcfg]);
		}

		// reset set model filter
		$this->resetFilter();

		// insert the remaining new configs from default config app
		foreach ( $app_default_config as $cfg_key => $cfg )
		{
			$data = new \stdClass();
			$dataapp = $app_name;
			$datacfg = $cfg_key;
			$dataval = $cfg[1];

			$this->addData($data);
		}

		// insert new configs
		$this->save();
	}

	public function saveConfig($data)
	{
		// Get config definition from app
		$app_config_def = App::create($dataapp)->getConfigDefinition();

		// Store the appname this config is for
		$app = $dataapp;

		// Remove appname and btn values from data
		unset($dataapp, $databtn_submit);

		// From here the app name is needed as underscored string
		String::uncamelize($app);

		// Set data to model so the validator has work to do
		$this->data = $data;

		// Get the keys from send data as fieldnames to check on validator
		$data_fld_list = array_keys(get_object_vars($data));

		// Add possible validatipons rules
		foreach ( $data_fld_list as $fld )
		{
			// try to get validation rules from config definition
			if (isset($app_config_def[$fld]['validate']))
				$this->addValidationRule($fld, $app_config_def[$fld]['validate']);
		}

		// Validate!
		$this->validate();

		// Was walidation a success or did we get some errors?
		if ($this->hasErrors())
			return;

			// No errors found. Delete the current app config from db
		$this->setFilter('app={string:app}', array(
			'app' => $app == 'admin' ? 'web' : $app
		));
		$this->delete();

		// The real config model is needed. ;)
		$config_model = ->appgetModel($this->name);

		// and now save the config values
		foreach ( $data as $fld => $val )
		{
			if ($val === '' && ( !isset($app_config_def[$fld]['default']) || ( isset($app_config_def[$fld]['default']) && $app_config_def[$fld]['default'] === '' ) ))
				continue;

			$cfg_data = new Data();

			$cfg_dataapp = $app;
			$cfg_datacfg = $fld;
			$cfg_dataval = $val;

			// Save config without further validation
			$config_modelsetData($cfg_data)save(false);
		}
	}
}
