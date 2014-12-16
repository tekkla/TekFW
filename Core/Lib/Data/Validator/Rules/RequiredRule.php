<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Required
 *
 * Checks for a field to be set and empty.
 * Note: Same as EmptyRule only with different errortext.
 */
class RequiredRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     */
    public function execute()
    {
        $result = ! empty($this->value);

        if (! $result) {
            $this->msg = $this->txt('web_validator_required');
        }
    }
}
