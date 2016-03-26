<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * BlankRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class BlankRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     */
    public function execute()
    {
        $result = $this->value !== '' ? true : false;

        if (! $result) {
            $this->msg = $this->text('validator.blank');
        }
    }
}
