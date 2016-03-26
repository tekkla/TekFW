<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * DateTimeRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class DateTimeRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     *
     */
    public function execute()
    {
        $result = strtotime($this->value) === false ? false : true;

        if (! $result) {
            $this->msg = $this->text('validator.datetime');
        }
    }
}
