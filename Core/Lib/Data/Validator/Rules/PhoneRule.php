<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Phone
 *
 * Checks the value to be a valid phone number
 */
class PhoneRule extends RuleAbstract
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
        $regexp = '/^([\+][0-9]{1,3}[\ \.\-])?([\(]{1}[0-9]{2,6}[\)])?([0-9\ \.\-\/]{3,20})((x|ext|extension)[\ ]?[0-9]{1,4})?$/';
        $result = empty($this->value) ? true : filter_var($this->value, FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => $regexp
            ]
        ]);

        if (! $result) {
            $this->msg = $this->txt('validator_phone');
        }
    }
}
