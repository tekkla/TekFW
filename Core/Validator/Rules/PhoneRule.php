<?php
namespace Core\Validator\Rules;

use Core\Validator\AbstractRule;

/**
 * PhoneRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class PhoneRule extends AbstractRule
{

    protected $execute_on_empty = false;

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Validator\Rules\AbstractRule::execute()
     *
     */
    public function execute()
    {
        $result = empty($this->value) ? true : filter_var($this->value, FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => '/^([\+][0-9]{1,3}[\ \.\-])?([\(]{1}[0-9]{2,6}[\)])?([0-9\ \.\-\/]{3,20})((x|ext|extension)[\ ]?[0-9]{1,4})?$/'
            ]
        ]);
        
        if (! $result) {
            $this->msg = 'validator.phone';
        }
    }
}
