<?php
namespace Core\Validator\Rules;

use Core\Validator\AbstractRule;
use Core\Validator\ValidatorException;

/**
 * CompareRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class CompareRule extends AbstractRule
{
    /**
     * (non-PHPdoc)
     *
     * @see \Core\Validator\Rules\AbstractRule::execute()
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
            $this->msg = [
                'validator.compare',
                $this->value,
                $to_compare_with,
                $mode
            ];
        }
    }
}
