<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Bigger or equal
 *
 * Compares the value against a compare value by type and lenghts.
 * Only strings and numbers can be compared. All othe types returns false.
 */
class BiggerOrEqualRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     */
    public function execute()
    {
        $to_compare = func_get_arg(0);

        if (is_string($this->value) && is_string($to_compare)) {
            $result = strlen($this->value) >= strlen($to_compare) ? true : false;
        }
        elseif ((is_int($this->value) && is_int($to_compare)) || (is_float($this->value) && is_float($to_compare))) {
            $result = $this->value >= $to_compare ? true : false;
        }
        else {
            $result = false;
        }

        if (! $result) {
            $this->msg = $this->txt('validator_bigger_or_equal');
        }
    }
}

