<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Only letter and number
 *
 * Checks the value to be only of letters and numbers
 */
class OnlyLetterNumberRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     *
     */
    public function execute()
    {
        $regexp = '/^[0-9a-zA-Z]+$/';

        $result = filter_var($this->value, FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => $regexp
            ]
        ]);

        if (! $result) {
            $this->msg = $this->txt('validator_alnum');
        }
    }
}
