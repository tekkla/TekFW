<?php
namespace Core\Lib\Content\Html\Form;

use Core\Lib\Content\Html\FormAbstract;

/**
 * Option Form Element
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2015 by author
 */
class Option extends FormAbstract
{

    protected $element = 'option';

    protected $data = [
        'control' => 'option'
    ];

    protected $attribute = [
        'value' => 1,
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

        if (! isset($state)) {
            return isset($this->attribute[$attrib]) ? $this->attribute[$attrib] : false;
        }

        if ($state == 0) {
            unset($this->attribute[$attrib]);
        }
        else {
            $this->attribute[$attrib] = false;
        }

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
        if ($value === null) {
            Throw new \InvalidArgumentException('Your are not allowed to set a NULL as value for a html option.');
        }

        $this->attribute['value'] = $value;

        return $this;
    }

    /**
     * Gets value of option
     *
     * @return \Core\Lib\Content\Html\Form\Option
     */
    public function getValue()
    {
        return isset($this->attribute['value']) ? $this->attribute['value'] : null;
    }

    public function build()
    {
        if (!$this->checkAttribute('value') && !empty($this->inner)) {
            $this->attribute['value'] = $this->inner;
        }

        if ($this->checkAttribute('value') && empty($this->inner)) {
            $this->inner = $this->attribute['value'];
        }

        return parent::build();
    }
}
