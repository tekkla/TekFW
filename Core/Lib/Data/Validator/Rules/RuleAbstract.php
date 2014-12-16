<?php
namespace Core\Lib\Data\Validator\Rules;

use Core\Lib\Traits\TextTrait;
use Core\Lib\Data\Validator\Validator;

/**
 * Abstract Rule Class
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2014
 * @version 1.0
 */
abstract class RuleAbstract
{

    use TextTrait;

    /**
     *
     * @var string Errortext
     */
    protected $msg = '';

    /**
     *
     * @var mixed Value to validate
     */
    protected $value;

    /**
     *
     * @var Validator
     */
    private $validator;

    /**
     * Constructor
     *
     * @param mixed $value Value to validate
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Sets value to check
     *
     * @param mixed $value
     *
     * @return \Core\Lib\Data\Validator\Rules\RuleAbstract
     */
    public function setValue($value)
    {
        // Reset rule object;
        $this->reset();

        // Assign value
        $this->value = $value;

        return $this;
    }

    /**
     * Checks for empty txt property and returns result as validation result.
     * Empty txt property means the validation check was successful.
     *
     * @return bool
     */
    public function isValid()
    {
        return empty($this->msg);
    }

    /**
     * Returns the stored message.
     *
     * @return string
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * Resets rule message.
     *
     * Will be called on rule creation process in validator object.No need to call it manually.
     *
     * @return RuleAbstract
     */
    public function reset()
    {
        // Reset old message;
        $this->msg = '';

        return $this;
    }

    /**
     * Creates a rule object.
     *
     * Ideal for rules where you need to combine or accees another rule.
     *
     * @param string $rule_name
     *
     * @return \Core\Lib\Data\Validator\Rules\RuleAbstract
     */
    protected function createRule($rule_name)
    {
        return $this->validator->createRule($rule_name);
    }

    /**
     * Validation method
     *
     * @param mixed Optional arguments
     */
    abstract public function execute();
}
