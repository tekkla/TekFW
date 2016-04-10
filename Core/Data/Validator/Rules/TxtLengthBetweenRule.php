<?php
namespace Core\Data\Validator\Rules;

/**
 * TxtLengthBetweenRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class TxtLengthBetweenRule extends RuleAbstract
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

        $value = (string) $this->value;

        $result = strlen($value) >= $min && strlen($value) <= $max;

        if (! $result) {
            $this->msg = sprintf($this->text('validator.textrange'), $min, $max, strlen($this->value));
        }
    }
}
