<?php
namespace Core\Lib\Content\Html\Controls;

use Core\Lib\Content\Html\FormAbstract;
use Core\Lib\Errors\Exceptions\UnexpectedValueException;

/**
 * OptionGroup.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class OptionGroup extends FormAbstract
{

    /**
     * Options storage
     *
     * @var array
     */
    private $options = [];

    /**
     * Add an option to the optionslist and returns a reference to it.
     *
     * @return Option
     */
    public function &createOption()
    {
        $unique_id = uniqid('option_');

        $this->options[$unique_id] = $this->factory->create('Form\Option');

        return $this->options[$unique_id];
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
        if (empty($this->options)) {
            Throw new UnexpectedValueException('OptionGroup Control: No Options set.');
        }

        $html = '';

        foreach ($this->options as $option) {

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
            if ($option->isSelected()) {
                $args['isChecked'] = 1;
            }

            // Create checkox
            $control = $this->factory->create('Form\Checkbox', $args);

            // Build control
            $html .= '
            <div class="checkbox">
                <label>' . $control->build() . $option->getInner() . '</label>
            </div>';
        }

        return $html;
    }
}
