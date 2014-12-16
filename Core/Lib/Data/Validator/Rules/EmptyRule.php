<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * Validator Rule: Empty
 *
 * Checks for empty value like the php function empty().
 */
class EmptyRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
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
            $this->msg = $this->txt('validator_empty');
        }
    }
}
