<?php
namespace Core\Validator\Rules;

use Core\Validator\AbstractRule;

/**
 * TxtMinLengthRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class TxtMinLengthRule extends AbstractRule
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

        $result = strlen((string) $this->value) >= $min;

        if (! $result) {
            $this->msg = [
                'validator.textminlength',
                $min
            ];
        }
    }
}
