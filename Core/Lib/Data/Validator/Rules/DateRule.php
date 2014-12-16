<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Date
 *
 * Checks the value to be valid date by trying to convert it into timestamp.
 * Note: Same as DateTimeRule only with different errortext.
 */
class DateRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     *
     */
    public function execute()
    {
        $result = strtotime($this->value) === false ? false : true;

        if (! $result) {
            $this->msg = $this->txt('validator_date');
        }
    }
}
