<?php
namespace Core\Data\Validator\Rules;

/**
 * IntegerRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class IntegerRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Data\Validator\Rules\RuleAbstract::execute()
     *
     */
    public function execute()
    {
        $result = is_int($this->value);

        if (! $result) {
            $this->msg = $this->text('validator.integer');
        }
    }
}