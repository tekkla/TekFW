<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Datetime
 *
 * Checks the value to be valid date/time by trying to convert it into timestamp.
 */
class DateTimeRule extends RuleAbstract
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
            $this->msg = $this->txt('validator_datetime');
        }
    }
}
