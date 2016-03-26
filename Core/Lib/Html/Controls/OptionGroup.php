<?php
namespace Core\Lib\Html\Controls;

use Core\Lib\Html\Elements\Div;
use Core\Lib\Html\HtmlException;

/**
 * OptionGroup.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class OptionGroup extends Div
{

    /**
     * Options storage
     *
     * @var array
     */
    protected $controls = [];

    /**
     * Add an option to the optionslist and returns a reference to it.
     *
     * @return \Core\Lib\Html\Form\Checkbox
     */
    public function &createOption($text = '', $value = '')
    {
        $option = $this->factory->create('Form\Checkbox');

        if ($text) {
            $option->setInner($text);
        }

        if ($value) {
            $option->setValue($value);
        }

        $this->controls[] = $option;

        return $option;
    }

    public function &createHeading($text, $size = 4)
    {
        $heading = $this->factory->create('Elements\Heading');
        $heading->setSize($size);
        $heading->setInner($text);

        $this->controls[] = $heading;

        return $heading;
    }

    /**
     * Builds the optiongroup control and returns the html code
     *
     * @see \Core\Lib\Html::build()
     *
     * @throws UnexpectedValueException
     *
     * @return string
     */
    public function build()
    {
        if (empty($this->controls) && empty($this->inner)) {
            Throw new HtmlException('OptionGroup Control: No Options set.');
        }

        /* @var $option \Core\Lib\Html\Form\Option */
        foreach ($this->controls as $option) {

            if ($option instanceof \Core\Lib\Html\Elements\Heading) {
                $this->inner .= $option->build();
                continue;
            }

            // Create name of optionelement
            $option_name = $this->getName() . '[' . $option->getValue() . ']';
            $option_id = $this->getId() . '_' . $option->getValue();

            $args = [
                'setName' => $option_name,
                'setId' => $option_id,
                'setValue' => $option->getValue(),
                'addAttribute' => [
                    'title' => $option->getInner()
                ]
            ];

            // If value is greater 0 this checkbox is selected
            if ($option->getSelected()) {
                $args['isChecked'] = 1;
            }

            // Create checkox
            $control = $this->factory->create('Form\Checkbox', $args);

            // Build control
            $this->inner .= '
            <div class="checkbox">
                <label>' . $control->build() . $option->getInner() . '</label>
            </div>';
        }

        return parent::build();
    }
}
