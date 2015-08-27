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
        $result = filter_var($this->value, FILTER_VALIDATE_EMAIL);

        list ($user, $domain) = explode("@", $this->value);

        // Perform dns check of mail domain
        $result = checkdnsrr($domain, "MX");

        if (! $result) {
            $this->msg = $this->txt('validator_email');
        }
    }
}
