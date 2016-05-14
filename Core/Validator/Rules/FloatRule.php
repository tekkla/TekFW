<?php
namespace Core\Validator\Rules;

use Core\Validator\AbstractRule;

/**
 * FloatRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class FloatRule extends AbstractRule
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Validator\Rules\AbstractRule::execute()
     *
     */
    public function execute()
    {
        $result = is_float($this->value);
        
        if (! $result) {
            $this->msg = 'validator.float';
        }
    }
}
