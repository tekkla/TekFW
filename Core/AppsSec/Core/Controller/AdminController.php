<?php
namespace Core\AppsSec\Admin\Controller;

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
            'config' => $this->url->compile('admin_app_config', [
                'app_name' => 'core'
            ]),
            'loaded_apps' => $this->model->getApplist()
        ]);

        $this->addLinktree('TekFW Framework Center');
    }
}
