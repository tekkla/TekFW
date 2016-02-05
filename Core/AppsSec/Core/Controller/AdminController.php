<?php
namespace Core\AppsSec\Core\Controller;

use Core\Lib\Amvc\Controller;

/**
 * AdminController.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
final class AdminController extends Controller
{

    public function Index()
    {
        $this->setVar([
            'loaded_apps' => $this->model->getApplist()
        ]);
        
        $this->page->breadcrumbs->createActiveItem('TekFW Framework Center');
    }
}
