<?php
namespace Core\Lib\Data\Validator\Rules;

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
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     *
     */
    public function execute()
    {
        $min = func_get_arg(0);
        $max = func_get_arg(1);

        $value = (string) $this->value;

        $result = strlen($value) >= $min && strlen($value) <= $max;

        if (! $result) {
            $this->msg = sprintf($this->txt('validator_textrange'), $min, $max, strlen($this->value));
        }
    }
}
