<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Date iso
 *
 * Compares the value aginst the ISO dateformat (YYYY-mm-dd).
 */
class DateIsoRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     *
     */
    public function execute()
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $this->value, $parts) == true) {

            $result = false;

            // Build time from parts
            $time = mktime(0, 0, 0, $parts[2], $parts[3], $parts[1]);

            // Build time from value
            $input_time = strtotime($this->value);

            // Compare both timestamps
            if ($input_time == $time) {
                $result = true;
            }
        }
        else {
            // No matches found
            $result = false;
        }

        if (! $result) {
            $this->msg = $this->txt('validator_date_iso');
        }
    }
}
