<?php
namespace Core\Lib\Content\Html\Form;

use Core\Lib\Content\Html\FormAbstract;

/**
 * Select Form Element
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Html\Form
 * @license MIT
 * @copyright 2014 by author
 */
class Select extends FormAbstract
{

    private $options = [];

    protected $element = 'select';

    protected $data = [
        'control' => 'select'
    ];

    private $value = [];

    /**
     * Creates an Option object and returns it
     *
     * @return \Core\Lib\Content\Html\Form\Option
     */
    public function &createOption()
    {
        $option = $this->factory->create('Form\Option');

        return $this->addOption($option);
    }

    /**
     * Add an Option object to the options array.
     * Use parameters to predefine
     * the objects settings. If inner parameter is not set, the value is the inner
     * content of option and has no value attribute.
     *
     * @param string|int $value
     * @param string|int Optional $inner
     * @param number $selected
     * @return \Core\Lib\Content\Html\Form\Select
     */
    public function &newOption($value = null, $inner = null, $selected = 0)
    {
        $option = $this->factory->create('Form\Option');

        $option->isSelected($selected);

        if (isset($value)) {
            $option->setValue($value);
        }

        if (isset($inner)) {
            $option->setInner($inner);
        }

        return $this->addOption($option);
    }

    /**
     * Add an html option object to the optionlist
     *
     * @param Option $option
     *
     * @return \Core\Lib\Content\Html\Form\Select
     */
    public function &addOption(Option $option)
    {
        $uniqeid = uniqid($this->getName() . '_option_');
        $this->options[$uniqeid] = $option;

        return $this->options[$uniqeid];
    }

    public function setSize($size)
    {
        if (! is_int($size)) {
            Throw new \InvalidArgumentException('A html form selects size attribute needs to be an integer.');
        }

        $this->addAttribute('size', $size);

        return $this;
    }

    public function isMultiple($state = null)
    {
        $attrib = 'multiple';

        if (! isset($state)) {
            return $this->checkAttribute($attrib);
        }

        if ($state == 0) {
            $this->removeAttribute($attrib);
        }
        else {
            $this->addAttribute($attrib, $attrib);
        }

        return $this;
    }

    public function setValue($value)
    {
        if (!is_array($value)) {
            $value = (array) $value;
        }

        $this->value = $value;

        return $this;
    }

    public function getValue()
    {
        $values = [];

        /* @var $option \Core\Lib\Content\Html\Form\Option */
        foreach ($this->options as $option) {
            if ($option->isSelected()) {
                $values[] = $option->getValue();
            }
        }

        return implode(',', $values);
    }

    public function build()
    {
        if (count($this->value) > 1) {
            $this->isMultiple(1);
        }

        foreach ($this->options as $option) {

            if (in_array($option->getValue(), $this->value)) {
                $option->isSelected(1);
            }

            $this->inner .= $option->build();
        }

        if ($this->isMultiple()) {
            $this->setName($this->getName() . '[]');
        }

        return parent::build();
    }
}
