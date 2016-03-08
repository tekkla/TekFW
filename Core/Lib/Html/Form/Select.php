<?php
namespace Core\Lib\Html\Form;

use Core\Lib\Html\FormAbstract;
use Core\Lib\Html\Form\Traits\IsMultipleTrait;
use Core\Lib\Html\Form\Traits\SizeTrait;
use Core\Lib\Html\Form\Traits\ValueTrait;

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
    use ValueTrait;

    private $options = [];

    protected $element = 'select';

    protected $data = [
        'control' => 'select'
    ];

    protected $value = [];

    /**
     * Creates an Option object and returns it
     *
     * @return \Core\Lib\Html\Form\Option
     */
    public function &createOption($optgroup = '')
    {
        $option = $this->factory->create('Form\Option');

        return $this->addOption($option, $optgroup);
    }

    /**
     * Add an Option object to the options array.
     *
     * Use parameters to predefine the objects settings.
     * If inner parameter is not set, the value is the inner
     * content of option and has no value attribute.
     *
     * @param string|int $value
     * @param
     *            string|int Optional $inner
     * @param boolean $selected
     *
     * @return \Core\Lib\Html\Form\Select
     */
    public function &newOption($value = null, $inner = null, $selected = false, $optgroup = '')
    {
        /* @var $option \Core\Lib\Html\Form\Option */
        $option = $this->factory->create('Form\Option');

        if (isset($value)) {
            $option->setValue($value);
        }

        if (isset($inner)) {
            $option->setInner($inner);
        }

        if ($selected == true) {
            $option->isSelected();
        }

        return $this->addOption($option, $optgroup);
    }

    /**
     * Add an html option object to the optionlist
     *
     * @param Option $option
     *
     * @return \Core\Lib\Html\Form\Select
     */
    public function &addOption(Option $option, $optgroup = '')
    {
        if (empty($optgroup)) {
            $this->options[] = $option;
        }
        else {
            $this->options[$optgroup][] = $option;
        }

        return $option;
    }

    private function buildOption(Option $option) {

        // Select unselected options when the options value is in selects value array
        if (! $option->getSelected() && in_array($option->getValue(), $this->value)) {
            $option->isSelected();
        }

        return $option->build();
    }

    public function build()
    {
        foreach ($this->options as $key => $option) {

            if (is_array($option)) {
                $this->inner .= '<optgroup label="' . $key . '">';

                foreach ($option as $opt) {
                    $this->inner .= $this->buildOption($opt);
                }

                $this->inner .= '</optgroup>';
            }
            else {
                $this->inner .= $this->buildOption($option);
            }
        }

        if ($this->getMultiple()) {
            $this->setName($this->getName() . '[]');
        }

        return parent::build();
    }
}
