<?php
namespace Core\Validator\Rules;

use Core\Validator\AbstractRule;

/**
 * EnumRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class EnumRule extends AbstractRule
{

    public function execute()
    {
        $result = in_array($this->value, func_get_args());

        if (! $result) {
            $this->msg = [
                'validator.enum',
                $this->value,
                implode(', ', func_get_args())
            ];
        }
    }
}
