<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Range
 *
 * Checks the value for the minimum and maximum length (string) or amount (number) given by the parameters
 */
class RangeRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     */
    public function execute()
    {
        // Which rule object shoud be used? Number or text?
        $rule_name = is_numeric($this->value) && func_get_arg(2) === false ? 'NumberRangeRule' : 'TxtLengthBetweenRule';

        $rule = $this->createRule($rule_name);
        $rule->setValue($this->value);
        $rule->execute(func_get_arg(0), func_get_arg(1));

        // Work with the result of check
        if (! $rule->isValid()) {
            $this->msg = $rule->getMsg();
        }
    }
}

