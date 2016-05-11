<?php
namespace Core\Validator\Rules;

use Core\Validator\AbstractRule;

/**
 * NumberMinRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class NumberMinRule extends AbstractRule
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

        $result = $this->value >= $min;

        if (! $result) {
            $this->msg = [
                'validator.numbermin',
                $min
            ];
        }
    }
}
