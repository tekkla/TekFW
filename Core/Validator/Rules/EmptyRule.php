<?php
namespace Core\Validator\Rules;

use Core\Validator\AbstractRule;

/**
 * EmptyRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class EmptyRule extends AbstractRule
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Validator\Rules\AbstractRule::execute()
     */
    public function execute()
    {
        $result = true;
        
        if (empty($this->value)) {
            
            if (! is_numeric($this->value)) {
                $result = false;
            }
        }
        
        if (! $result) {
            $this->msg = 'validator.empty';
        }
    }
}
