<?php
namespace Core\Validator\Rules;

use Core\Validator\AbstractRule;

/**
 * TxtLengthBetweenRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class TxtLengthBetweenRule extends AbstractRule
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Validator\Rules\AbstractRule::execute()
     *
     */
    public function execute()
    {
        $min = func_get_arg(0);
        $max = func_get_arg(1);

        $value = (string) $this->value;

        $result = strlen($value) >= $min && strlen($value) <= $max;

        if (! $result) {
            $this->msg = [
                'validator.textrange',
                $min,
                $max,
                strlen($this->value)
            ];
        }
    }
}
