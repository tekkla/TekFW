<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * EmailRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class EmailRule extends RuleAbstract
{

    protected $execute_on_empty = false;

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
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
                    $txt = 'validator_email_dnscheck';
                }
            }
        }

        if (! $result) {
            $this->msg = sprintf($this->txt($txt), $domain);
        }
    }
}
