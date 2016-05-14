<?php
namespace Core\Validator\Rules;

use Core\Validator\AbstractRule;

/**
 * BlankRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class BlankRule extends AbstractRule
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Validator\Rules\AbstractRule::execute()
     */
    public function execute()
    {
        $result = $this->value !== '' ? true : false;
        
        if (! $result) {
            $this->msg = 'validator.blank';
        }
    }
}
