<?php
namespace Core\Data\Validator\Rules;

/**
 * MinRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class MinRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Data\Validator\Rules\RuleAbstract::execute()
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

