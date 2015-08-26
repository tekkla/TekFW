<?php
namespace Core\Lib\Content\Html\Form;

use Core\Lib\Content\Html\FormAbstract;
use Core\Lib\Content\Html\Form\Traits\IsMultipleTrait;
use Core\Lib\Content\Html\Form\Traits\SizeTrait;

/**
 * Select.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Select extends FormAbstract
{
    use IsMultipleTrait;
    use SizeTrait;

    private $options = [];

    protected $element = 'select';

    protected $data = [
        'control' => 'select'
    ];

    private $value = null;

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

    /**
     * Sets values to be selected.
     *
     * @param array $value
     */
    public function setValue($value)
    {
        if (! is_array($value)) {
            $value = (array) $value;
        }

        $this->value = $value;

        return $this;
    }

    /**
     * Returns set value
     *
     * @return Ambigous <array, array>
     */
    public function getValue()
    {
        return $this->value;
    }

    public function build()
    {
        if (count($this->value) > 1) {
            $this->isMultiple(1);
        }

        foreach ($this->options as $option) {

            if ($this->value !== null && in_array($option->getValue(), $this->value)) {
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
