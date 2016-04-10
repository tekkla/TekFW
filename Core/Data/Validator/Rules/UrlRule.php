<?php
namespace Core\Data\Validator\Rules;

/**
 * UrlRule.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class UrlRule extends RuleAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Data\Validator\Rules\RuleAbstract::execute()
     *
     */
    public function execute()
    {
        $result = filter_var($this->value, FILTER_VALIDATE_URL);

        if ($result===false) {
            $this->msg = $this->text('validator.url');
        }
    }
}
