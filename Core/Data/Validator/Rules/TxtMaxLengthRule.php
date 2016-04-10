<?php
namespace Core\Data\Validator\Rules;

/**
 * TxtMaxLengthRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class TxtMaxLengthRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Data\Validator\Rules\RuleAbstract::execute()
     *
     */
    public function execute()
    {
        $max = func_get_arg(0);

        $result = strlen((string) $this->value) <= $max;

        if (! $result) {
            $this->msg = sprintf($this->text('validator.textmaxlength'), $max);
        }
    }
}
