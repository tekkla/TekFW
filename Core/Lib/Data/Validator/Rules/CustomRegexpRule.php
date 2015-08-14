<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Custom Regexp
 *
 * Checks the value against a custom regexp.
 */
class CustomRegexpRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     */
    public function execute()
    {
        $regexp = func_get_arg(0);

        $result = filter_var($this->value, FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => $regexp
            ]
        ]);

        if (! $result) {
            $this->msg = $this->txt('validator_customregex');
        }
    }
}

