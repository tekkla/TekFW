<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: TxtMaxLength
 *
 * Checks the length of the value against the given lenght ($max).
 */
class TxtMaxLengthRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     *
     */
    public function execute()
    {
        $max = func_get_arg(0);

        $result = strlen((string) $this->value) <= $max;

        if (! $result) {
            $this->msg = sprintf($this->txt('validator_textmaxlength'), $max);
        }
    }
}
