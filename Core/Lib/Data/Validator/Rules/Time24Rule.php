<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Time (24h format)
 *
 * Checks the value against the 24-hour notation.
 */
class PhoneRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     *
     */
    public function execute()
    {
        $regexp = '/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/';
        $result = filter_var($this->value, FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => $regexp
            ]
        ]);

        if (! $result) {
            $this->msg = $this->txt('validator_time24');
        }
    }
}
