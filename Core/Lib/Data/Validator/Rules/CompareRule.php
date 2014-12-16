<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Compare
 *
 * Checks the value against a comparision value.
 * The comparemode can be defined by use.
 */
class CompareRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     */
    public function execute()
    {
        $to_compare_with = func_get_arg(0);
        $mode = func_num_args() == 1 ? '=' : func_get_arg(1);

        $modes = [
            '=',
            '>',
            '<',
            '>=',
            '<='
        ];

        if (! in_array($mode, $modes))
            Throw new \InvalidArgumentException(sprintf('Parameter "%s" not allowed', $mode), 1001);

        switch ($mode) {
            case '=':
                $result = $this->value == $to_compare_with;
                break;

            case '>':
                $result = $this->value > $to_compare_with;
                break;

            case '<':
                $result = $this->value < $to_compare_with;
                break;

            case '>=':
                $result = $this->value >= $to_compare_with;
                break;

            case '<=':
                $result = $this->value <= $to_compare_with;
                break;
        }

        if (! $result) {
            $this->msg = sprintf($this->txt('validator_compare'), $this->value, $to_compare_with, $mode);
        }
    }
}
