<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Only number
 *
 * Checks the value to be only of numbers
 */
class OnlyNumberRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     *
     */
    public function execute()
    {
        $regexp = '/^[0-9\ ]+$/';
        $result = filter_var($this->value, FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => $regexp
            ]
        ]);
        
        if (! $result) {
            $this->msg = $this->txt('validator_number');
        }
    }
}
