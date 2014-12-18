<?php
namespace Core\Lib\Content\Html\Form;

use Core\Lib\Content\Html\FormAbstract;

/**
 * Option Form Element
 * 
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Html\Form
 * @license MIT
 * @copyright 2014 by author
 */
class Option extends FormAbstract
{

    protected $element = 'option';

    protected $data = [
        'control' => 'option'
    ];

    /**
     * Selected attribute setter and checker.
     * Accepts parameter "null", "0" and "1".
     * "null" means to check for a set disabled attribute
     * "0" means to remove disabled attribute
     * "1" means to set disabled attribute
     * 
     * @param int $state
     * @return \Core\Lib\Content\Html\Form\Option
     */
    public function isSelected($state = null)
    {
        $attrib = 'selected';
        
        if (! isset($state))
            return $this->checkAttribute($attrib);
        
        if ($state == 0)
            $this->removeAttribute($attrib);
        else
            $this->addAttribute($attrib, false);
        
        return $this;
    }

    /**
     * Sets value of option
     * 
     * @param string|number $value
     * @return \Core\Lib\Content\Html\Form\Option
     */
    public function setValue($value)
    {
        if ($value === null)
            Throw new \InvalidArgumentException('Your are not allowed to set a NULL as value for a html option.');
        
        $this->addAttribute('value', $value);
        return $this;
    }

    /**
     * Gets value of option
     * 
     * @return \Core\Lib\Content\Html\Form\Option
     */
    public function getValue()
    {
        return $this->getAttribute('value');
    }
}
