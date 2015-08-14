<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: TxtMaxLength
 *
 * Checks the length of the value to be within min ($min) and max ($max) lenght.
 */
class TxtLengthBetweenRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     *
     */
    public function execute()
    {
        $min = func_get_arg(0);
        $max = func_get_arg(1);

        $value = (string) $this->value;

        $result = strlen($value) >= $min && strlen($value) <= $max;

        if (! $result) {
            $this->msg = sprintf($this->txt('validator_textrange'), $min, $max, strlen($this->value));
        }
    }
}
