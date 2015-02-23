<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Integer
 *
 * Checks the value to be a valid email adress
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

        /*
         * @TODO
         *
         * list($userName, $mailDomain) = explode("@", $email);
         * if (!checkdnsrr($mailDomain, "MX")) {
         *     // Email is unreachable.
         * }
         */
        if (! $result) {
            $this->msg = $this->txt('validator_email');
        }
    }
}
