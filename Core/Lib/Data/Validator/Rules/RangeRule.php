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
        $rule_name = func_num_args() == 3 && func_get_arg(2) === true ? 'TxtLengthBetween' : 'NumberRange';

        $rule = $this->createRule($rule_name);
        $rule->setValue($this->value);

        $args = func_get_arg(0);

        $rule->execute(func_get_arg(0), func_get_arg(1));

        // Work with the result of check
        if (! $rule->isValid()) {
            $this->msg = $rule->getMsg();
        }
    }
}

