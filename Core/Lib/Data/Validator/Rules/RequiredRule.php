<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * RequiredRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class RequiredRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     */
    public function execute()
    {
        $result = ! empty($this->value);

        if (! $result) {
            $this->msg = $this->txt('validator_required');
        }
    }
}
