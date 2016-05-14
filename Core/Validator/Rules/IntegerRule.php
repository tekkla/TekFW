<?php
namespace Core\Validator\Rules;

use Core\Validator\AbstractRule;

/**
 * IntegerRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class IntegerRule extends AbstractRule
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Validator\Rules\AbstractRule::execute()
     *
     */
    public function execute()
    {
        $result = is_int($this->value);
        
        if (! $result) {
            $this->msg = 'validator.integer';
        }
    }
}
