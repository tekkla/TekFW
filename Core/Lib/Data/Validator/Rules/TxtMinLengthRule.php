<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: TxtMinLength
 *
 * Check for a minimum text length.
 */
class TxtMinLengthRule extends RuleAbstract
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

        $result = strlen((string) $this->value) >= $min;

        if (! $result) {
            $this->msg = sprintf($this->txt('validator_textminlength'), $min);
        }
    }
}
