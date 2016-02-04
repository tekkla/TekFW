<?php
namespace Core\Lib\Data\Validator\Rules;

/**
 * OnlyLetterNumberRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class OnlyLetterNumberRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Validator\Rules\RuleAbstract::execute()
     *
     */
    public function execute()
    {
        $regexp = '/^[0-9a-zA-Z]+$/';

        $result = filter_var($this->value, FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => $regexp
            ]
        ]);

        if (! $result) {
            $this->msg = $this->text('validator.alnum');
        }
    }
}
