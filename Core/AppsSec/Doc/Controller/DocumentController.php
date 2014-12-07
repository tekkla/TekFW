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
class DocumentController extends Controller
{

    public function Edit($id_document = NULL)
    {
        $post = $this->router->getPost();
        
        if ($post) {
            var_dump($post);
            exit();
        }
        
        if ($this->model->hasNoData())
            $this->model->getDoc($id_document);

		$form = ->
        getFormDesigner();
        
        /* @var Dataselect $control-> */
        $control = $form->createElement('dataselect', 'id_group')->setDatasource('Doc', 'Group', 'getGroupSelection');
        
        if ($this->model->dataid_group)
            $control->setSelectdValue($this->model->dataid_group);
        
        $form->createElement('number', 'position');
        $form->createElement('text', 'headline');
        $form->createElement('editor', 'content');
        
        $this->setVar('form', $form);
    }
}
