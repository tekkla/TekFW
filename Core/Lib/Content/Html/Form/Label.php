<?php
namespace Core\Lib\Content\Html\Form;

use Core\Lib\Content\Html\FormAbstract;

/**
 * Label Form Element
 * 
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Html\Form
 * @license MIT
 * @copyright 2014 by author
 */
class Label extends FormAbstract
{

    protected $element = 'label';

    public static function factory($for, $inner = null)
    {
        $obj = new Label();
        $obj->setFor($for);
        
        if (isset($inner))
            $obj->setInner($inner);
        else
            $obj->setInner($for);
        
        return $obj;
    }

    public function setFor($for)
    {
        $this->removeAttribute('for');
        $this->addAttribute('for', $for);
        return $this;
    }
}

