<?php
namespace Lib\Data\Rules;

use Core\Lib\Amvc\Model;
/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 *
 */
class RequiredRule
{
	/**
	 * Checks for a field to be set and empty.
	 */
	private function validate()
	{
		$this->result = isset($this->model->data->{$this->field});
		$this->error = $this->txt('web_validator_required');
	}
}

?>