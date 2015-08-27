<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * NumberMaxRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class NumberMaxRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     *
     */
    public function execute()
    {
        $max = func_get_arg(0);

        $result = $this->value <= $max;

        if (! $result) {
            $this->msg = sprintf($this->txt('validator_numbermax'), $max);
        }
    }
}
