<?php
namespace Core\Lib\Content\Html\Form;

use Core\Lib\Content\Html\FormAbstract;
use Core\Lib\Content\Html\Form\Traits\ValueTrait;

/**
 * Option Form Element
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2015 by author
 */
class Option extends FormAbstract
{
    use ValueTrait;

    protected $element = 'option';

    protected $data = [
        'control' => 'option'
    ];

    protected $attribute = [
        'value' => 1
    ];

    private $selected = false;

    /**
     * Set option to be selected.
     *
     * @return \Core\Lib\Content\Html\Form\Option
     */
    public function isSelected()
    {
        $this->selected = true;

        return $this;
    }

    /**
     * Set option to not selected.
     *
     * @return \Core\Lib\Content\Html\Form\Option
     */
    public function notSelected()
    {
        $this->selected = false;

        return $this;
    }

    /**
     * Returns options selcted state.
     */
    public function getSelected()
    {
        return $this->selected;
    }

    public function build()
    {
        if ($this->selected) {
            $this->attribute['selected'] = false;
        }

        if (! $this->checkAttribute('value') && ! empty($this->inner)) {
            $this->attribute['value'] = $this->inner;
        }

        if ($this->checkAttribute('value') && empty($this->inner)) {
            $this->inner = $this->attribute['value'];
        }

        return parent::build();
    }
}
