<?php
namespace Core\AppsSec\Core\Controller;

use Core\Lib\Amvc\Controller;

/**
 * Admin Controller
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 */
final class AdminController extends Controller
{

	public function Index()
	{
		$this->setVar([
			'config' => $this->router->url('core_config', [
				'app_name' => 'core'
			]),
			'loaded_apps' => $this->model->getApplist()
		]);

		$this->content->breadcrumbs->createActiveItem('TekFW Framework Center');
	}
}
