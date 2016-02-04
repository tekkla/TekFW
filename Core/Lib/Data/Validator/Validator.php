<?php
namespace Core\Lib\Data\Validator;

use Core\Lib\Language\TextTrait;
use Core\Lib\Traits\StringTrait;
use Core\Lib\Data\Validator\Rules\RuleAbstract;

/**
 * Validator.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
final class Validator
{
    use TextTrait;
    use StringTrait;

    /**
     *
     * @var array
     */
    private $msg = [];

    /**
     *
     * @var array
     */
    private $rules = [];

    /**
     * Constructor
     */
    public function __construct()
    {}

    /**
     * Validates a value against the wanted rules.
     *
     * @param mixed $value
     *            The value to validate
     * @param string|array $rules
     *            One or more rules
     *
     * @return multitype:
     */
    public function validate($value, $rules)
    {
        // Our current value (trimmed)
        $value = trim($value);

        if (! is_array($rules)) {
            $rules = (array) $rules;
        }

        // Reset present messages
        $this->msg = [];

        // Validate each rule against the
        foreach ($rules as $rule) {

            // Reset the last result
            $result = false;

            // Array type rules are for checks where the func needs one or more parameter
            // So $rule[0] is the func name and $rule[1] the parameter.
            // Parameters can be of type array where the elements are used as function parameters in the .. they are
            // set.
            if (is_array($rule)) {

                // Get the functionname
                $rule_name = $this->stringCamelize($rule[0]);

                // Parameters set?
                if (isset($rule[1])) {
                    $args = ! is_array($rule[1]) ? [
                        $rule[1]
                    ] : $rule[1];
                } else {
                    $args = [];
                }

                // Custom error message
                if (isset($rule[2])) {
                    $custom_message = $rule[2];
                }
            } else {
                $rule_name = $this->stringCamelize($rule);
                $args = [];
                unset($custom_message);
            }

            // Call rule creation process to make sure rule exists before starting further actions.
            /* @var $rule \Core\Lib\Data\Validator\Rules\RuleAbstract */
            $rule = $this->createRule($rule_name);

            // Execute rule on empty values only when rule is explicitly flagged to do so.
            if (empty($value) && $rule->getExecuteOnEmpty() == false) {
                continue;
            }

            $rule->setValue($value);

            // Calling the validation function
            call_user_func_array(array(
                $rule,
                'execute'
            ), $args);

            // Get result from rule
            $result = $rule->isValid();

            // Is the validation result negative eg false?
            if ($result === false) {

                // Get msg from rule
                $msg = $rule->getMsg();

                // If no error message is set, use the default validator error
                if (empty($msg)) {
                    $msg = isset($custom_message) ? $this->text($custom_message) : $this->text('validator.error');
                }

                $this->msg[] = htmlspecialchars($msg, ENT_COMPAT, 'UTF-8');
            }
        }

        return $this->msg;
    }

    /**
     * Returns the last validation result
     *
     * @return bool
     */
    public function isValid()
    {
        return empty($this->msg);
    }

    /**
     * Returns the last validation msg
     *
     * @return array
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * Creates and returns a rule object
     *
     * @param string $rule_name
     *            Name of the rule
     *
     * @return RuleAbstract
     */
    public function &createRule($rule_name)
    {
        // Rules have to be singletons
        if (! array_key_exists($rule_name, $this->rules)) {

            // Without a leading \ in the rulename it is assumened that we use a Core FW builtin rule
            // otherwise the $rule_name points to a class somewhere outsite of the frasmworks default rules.
            $rule_class = strpos($rule_name, '\\') == 0 ? '\Core\Lib\Data\Validator\Rules\\' . $rule_name . 'Rule' : $rule_name;

            // Create the rule obejct instance
            $rule_object = new $rule_class($this);

            // The rule object must be a child of RuleAbstract!
            if (! $rule_object instanceof RuleAbstract) {
                Throw new ValidatorException('Validator rules MUST BE a child of RuleAbstract');
            }

            // Add rule to the rules stack
            $this->rules[$rule_name] = new $rule_class($this);
        } else {

            // Reset existing rules
            $this->rules[$rule_name]->reset();
        }

        return $this->rules[$rule_name];
    }
}
