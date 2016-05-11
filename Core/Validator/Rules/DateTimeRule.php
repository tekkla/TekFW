<?php
namespace Core\Validator\Rules;

use Core\Validator\AbstractRule;

/**
 * DateTimeRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class DateTimeRule extends AbstractRule
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Validator\Rules\AbstractRule::execute()
     *
     */
    public function execute()
    {
        $result = strtotime($this->value) === false ? false : true;
        
        if (! $result) {
            $this->msg = 'validator.datetime';
        }
    }
}
