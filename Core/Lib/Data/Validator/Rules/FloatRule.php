<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Float
 *
 * Checks the value to be of type float
 */
class FloatRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     *
     */
    public function execute()
    {
        $result = is_float($this->value);
        
        if (! $result) {
            $this->msg = $this->txt('validator_float');
        }
    }
}
