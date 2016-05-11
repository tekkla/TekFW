<?php
namespace Core\Validator\Rules;

use Core\Validator\AbstractRule;

/**
 * IpV4Rule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class IpV4Rule extends AbstractRule
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Validator\Rules\AbstractRule::execute()
     *
     */
    public function execute()
    {
        $regexp = '/^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/';
        $result = filter_var($this->value, FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => $regexp
            ]
        ]);
        
        if (! $result) {
            $this->msg = 'validator.ipv4';
        }
    }
}
