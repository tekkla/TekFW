<?php
namespace Core\Validator\Rules;

use Core\Validator\AbstractRule;

/**
 * EmailRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class EmailRule extends AbstractRule
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
        $txt = 'validator_email';
        $domain = '';

        $result = filter_var($this->value, FILTER_VALIDATE_EMAIL);

        if ($result) {

            list ($user, $domain) = explode("@", $this->value);

            // Perform dns check of mail domain
            if ($domain) {

                $result = checkdnsrr($domain, "MX");

                if (! $result) {
                    $txt = 'validator.email.dnscheck';
                }
            }
        }

        if (! $result) {
            $this->msg = [
                $txt,
                $domain
            ];
        }
    }
}
