<?php
namespace Core\AppsSec\Admin\Controller;

use Core\Lib\Amvc\Controller;
use Core\Lib\Url;

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
        $this->setVar(array(
            'config' => Url::factory('admin_app_config', array(
                'app_name' => 'web'
            ))->getUrl(),
            'loaded_apps' => $this->model->getApplist()
        ));
        
        $this->addLinktree('TekFW Framework Center');
    }
}
