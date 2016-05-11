<?php
namespace Core\Validator\Rules;

use Core\Validator\AbstractRule;

/**
 * NumberMaxRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class NumberMaxRule extends AbstractRule
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

        $result = $this->value <= $max;

        if (! $result) {
            $this->msg = [
                'validator.numbermax',
                $max
            ];
        }
    }
}
