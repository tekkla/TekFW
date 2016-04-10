<?php
namespace Core\Data\Validator\Rules;

/**
 * CustomRegexpRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class CustomRegexpRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Data\Validator\Rules\RuleAbstract::execute()
     */
    public function execute()
    {
        $regexp = func_get_arg(0);

        $result = filter_var($this->value, FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => $regexp
            ]
        ]);

        if (! $result) {
            $this->msg = $this->text('validator.customregex');
        }
    }
}
