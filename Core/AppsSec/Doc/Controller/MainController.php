<?php
namespace Core\AppsSec\Doc\Controller;

use Core\Lib\Amvc\Controller;

/**
 * Description
 * 
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Global
 * @license MIT
 * @copyright 2014 by author
 */
class MainController extends Controller
{

    public function Index()
    {
        $this->setVar('content', 'start');
        
        $this->setVar('menu', $this->model->createMenu($this->request->getParam('page')));
    }
}
