<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Only letter
 *
 * Checks the value to be only of letters
 */
class OnlyLetterRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     *
     */
    public function execute()
    {
        $regexp = '/^[a-zA-Z\ \']+$/';
        $result = filter_var($this->value, FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => $regexp
            ]
        ]);

        if (! $result) {
            $this->msg = $this->txt('validator_alpha');
        }
    }
}
