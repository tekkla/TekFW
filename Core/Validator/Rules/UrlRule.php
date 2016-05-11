<?php
namespace Core\Validator\Rules;

use Core\Validator\AbstractRule;

/**
 * UrlRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class UrlRule extends AbstractRule
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Validator\Rules\AbstractRule::execute()
     *
     */
    public function execute()
    {
        $result = filter_var($this->value, FILTER_VALIDATE_URL);
        
        if ($result === false) {
            $this->msg = 'validator.url';
        }
    }
}
