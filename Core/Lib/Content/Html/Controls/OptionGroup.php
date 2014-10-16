<?php
namespace Core\Lib\Content\Html\Controls;

use Core\Lib\Abstracts\FormElementAbstract;

/**
 * Creates a optiongroup control
 * It is a set of checkboxes grouped together.
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Html\Controls
 * @license MIT
 * @copyright 2014 by author
 */
final class OptionGroup extends FormElementAbstract
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
	 * @return string
	 */
	public function build()
	{
		if (empty($this->options)) {
			Throw new \RuntimeException('OptionGroup Control: No Options set.');
		}

		$html = '';

		foreach ($this->options as $option) {

			$html .= '<div class="checkbox">';

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
			$html .= '<label>' . $control->build() . $option->getInner() . '</label>';

			$html .= '</div>';
		}

		return $html;
	}
}
