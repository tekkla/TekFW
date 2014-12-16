<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Min
 *
 * Checks the values for a minimum length (string) or amount (numeric).
 */
class MinRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     */
    public function execute()
    {
        // Which rule object shoud be used? Number or text?
        if (is_numeric($this->value) && func_get_arg(1) === false) {
            $rule = new NumberMinRule();
        }
        else {
            $rule = new TxtMinLengthRule();
        }

        $rule->setValue($this->value);

        // Execute rule
        $rule->execute(func_get_arg(0));

        // Work with the result of check
        if (! $rule->isValid()) {
            $this->msg = $rule->getMsg();
        }
    }
}

