<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Equals
 *
 * Checks the value against a compare value to proof both to be equal in type and value.
 * Compares only strings and numbers. All other types will return false.
 */
class EqualsRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     */
    public function execute()
    {
        $to_compare = func_get_arg(0);

        if (is_string($this->value) && is_string($to_compare)) {
            $result = $this->value == $to_compare ? true : false;
        }
        elseif ((is_int($this->value) && is_int($to_compare)) || (is_float($this->value) && is_float($to_compare))) {
            $result = $this->value == $to_compare ? true : false;
        }
        else {
            $result = false;
        }

        if (!$result) {
            $this->msg = $this->txt('validator_equals');
        }
    }
}
