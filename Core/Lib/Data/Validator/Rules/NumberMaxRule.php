<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Number max
 *
 * Check the value to be smaller or equal to parameter ($max)
 */
class NumberMaxRule extends RuleAbstract
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

        $result = $this->value <= $max;

        if (! $result) {
            $this->msg = sprintf($this->txt('validator_numbermax'), $max);
        }
    }
}
