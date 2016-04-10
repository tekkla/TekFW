<?php
namespace Core\Data\Validator\Rules;

/**
 * EmptyRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class EmptyRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Data\Validator\Rules\RuleAbstract::execute()
     */
    public function execute()
    {
        $result = true;

        if (empty($this->value)) {

            if (! is_numeric($this->value)) {
                $result = false;
            }
        }

        if (! $result) {
            $this->msg = $this->text('validator.empty');
        }
    }
}
