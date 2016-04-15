<?php
namespace Core\Data\Validator\Rules;

use Core\Data\Validator\ValidatorException;

/**
 * CompareRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class CompareRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Data\Validator\Rules\RuleAbstract::execute()
     *
     * @throws InvalidArgumentException
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

        if (! in_array($mode, $modes)) {
            Throw new ValidatorException(sprintf('Parameter "%s" not allowed', $mode), 1001);
        }

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
            $this->msg = sprintf($this->text('validator.compare'), $this->value, $to_compare_with, $mode);
        }
    }
}
