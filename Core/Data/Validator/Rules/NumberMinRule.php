<?php
namespace Core\Data\Validator\Rules;

/**
 * NumberMinRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class NumberMinRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Data\Validator\Rules\RuleAbstract::execute()
     *
     */
    public function execute()
    {
        $min = func_get_arg(0);

        $result = $this->value >= $min;

        if (! $result) {
            $this->msg = sprintf($this->text('validator.numbermin'), $min);
        }
    }
}
