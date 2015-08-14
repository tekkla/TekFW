<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Blank
 *
 * Checks for empty value but treats 0, -0, 0.0 as existing values.
 */
class BlankRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     */
    public function execute()
    {
        $result = $this->value !== '' ? true : false;

        if (! $result) {
            $this->msg = $this->txt('validator_blank');
        }
    }
}
