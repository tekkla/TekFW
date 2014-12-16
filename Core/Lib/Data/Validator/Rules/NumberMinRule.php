<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Number max
 *
 * Check the value to be bigger or equal to parameter ($min)
 */
class NumberMinRule extends RuleAbstract
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

        $result = $this->value <= $min;

        if (! $result) {
            $this->msg = sprintf($this->txt('validator_numbermin'), $min);
        }
    }
}
