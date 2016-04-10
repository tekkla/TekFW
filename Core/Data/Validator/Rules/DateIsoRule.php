<?php
namespace Core\Data\Validator\Rules;

/**
 * DateIsoRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class DateIsoRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Data\Validator\Rules\RuleAbstract::execute()
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
            $this->msg = $this->text('validator.date_iso');
        }
    }
}
