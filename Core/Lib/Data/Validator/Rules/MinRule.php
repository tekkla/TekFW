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
        // String compare?
        $string_compare = func_num_args() == 1 || func_get_arg(1) === false ? false : true;

        // Which rule object shoud be used? Number or text?
        $rule_name = is_numeric($this->value) && $string_compare === false ? 'NumberMin' : 'TxtMinLength';

        $rule = $this->createRule($rule_name);
        $rule->setValue($this->value);
        $rule->execute(func_get_arg(0));

        // Work with the result of check
        if (! $rule->isValid()) {
            $this->msg = $rule->getMsg();
        }
    }
}

