<?php
namespace Core\Validator\Rules;

use Core\Validator\AbstractRule;

/**
 * CustomRegexpRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class CustomRegexpRule extends AbstractRule
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Validator\Rules\AbstractRule::execute()
     */
    public function execute()
    {
        $regexp = func_get_arg(0);
        
        $result = filter_var($this->value, FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => $regexp
            ]
        ]);
        
        if (! $result) {
            $this->msg = 'validator.customregex';
        }
    }
}
