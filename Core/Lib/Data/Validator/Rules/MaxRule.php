<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Max
 *
 * Checks the values for a aximum length (string) or amount (numeric).
 */
class MaxRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     */
    public function execute()
    {
        // Which rule object shoud be used? Number or text?
        $rule_name = is_numeric($this->value) && func_get_arg(1) === false ? 'NumberMax' : 'TxtMaxLength';

        $rule = $this->createRule($rule_name);
        $rule->setValue($this->value);
        $rule->execute(func_get_arg(0));

        // Work with the result of check
        if (! $rule->isValid()) {
            $this->msg = $rule->getMsg();
        }
    }
}

