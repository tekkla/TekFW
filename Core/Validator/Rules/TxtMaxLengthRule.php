<?php
namespace Core\Validator\Rules;

use Core\Validator\AbstractRule;

/**
 * TxtMaxLengthRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class TxtMaxLengthRule extends AbstractRule
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Validator\Rules\AbstractRule::execute()
     *
     */
    public function execute()
    {
        $max = func_get_arg(0);

        $result = strlen((string) $this->value) <= $max;

        if (! $result) {
            $this->msg = [
                'validator.textmaxlength',
                $max
            ];
        }
    }
}
