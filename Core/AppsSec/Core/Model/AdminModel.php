<?php
namespace Core\AppsSec\Core\Model;

use Core\Lib\Amvc\Model;

/**
 *
 * @author Michael "Tekkla" Zorn
 *
 */
class AdminModel extends Model
{

	public function getApplist()
	{
		$applist = $this->di->get('core.amvc.creator')->getLoadedApps();

		sort($applist);

		$out = new \stdClass();

		foreach ($applist as $app_name) {

			$app = $this->di->get('core.amvc.creator')->getAppInstance($app_name);

			$app_data = new \stdClass();

			$app_data->config_link = '';

			#$app_data->config_link = isset($appconfig) ? Url::factory('admin_app_config')->setParameter('app_name', String::uncamelize($app_name))->getUrl() : false;

			$out->{$app_name} = $app_data;
		}

		return $out;
	}
}

