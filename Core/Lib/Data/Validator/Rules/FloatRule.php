<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * FloatRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class FloatRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     *
     */
    public function execute()
    {
        $result = is_float($this->value);

        if (! $result) {
            $this->msg = $this->text('validator.float');
        }
    }
}
