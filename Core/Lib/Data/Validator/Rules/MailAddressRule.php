<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Integer
 *
 * Checks the value to be a valid email adress
 */
class MailAddressRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     *
     */
    public function execute()
    {
        $result = filter_var($this->value, FILTER_VALIDATE_EMAIL);

        if (! $result) {
            $this->msg = $this->txt('validator_email');
        }
    }
}
