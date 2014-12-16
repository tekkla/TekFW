<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Integer
 *
 * Checks the value to be of type integer
 */
class IntegerRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     *
     */
    public function execute()
    {
        $result = is_int($this->value);

        if (! $result) {
            $this->msg = $this->txt('validator_integer');
        }
    }
}
