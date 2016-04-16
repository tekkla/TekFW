<?php
namespace Core\Data\Validator\Rules;

/**
 * EnumRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class EnumRule extends RuleAbstract
{

    public function execute()
    {
        $result = in_array($this->value, func_get_args());

        if (! $result) {
            $this->msg = sprintf($this->text('validator.enum'), $this->value, implode(', ', func_get_args()));
        }
    }
}
