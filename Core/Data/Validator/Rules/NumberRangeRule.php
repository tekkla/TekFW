<?php
namespace Core\Data\Validator\Rules;

/**
 * NumberRangeRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class NumberRangeRule extends RuleAbstract
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
        $max = func_get_arg(1);

        $result = $this->value >= $min && $this->value <= $max;

        if (! $result) {
            $this->msg = sprintf($this->text('validator.numberrange'), $min, $max);
        }
    }
}
