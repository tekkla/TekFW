<?php
namespace Core\Lib\Data\Validator;

use Core\Lib\Traits\TextTrait;
use Core\Lib\Traits\StringTrait;
use Core\Lib\Data\Validator\Rules\RuleAbstract;

/**
 * Validator
 *
 * Validates fields of a Container object against their validation rules.
 * Adde errors on failed validation to Container errorlist.
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license MIT
 * @todo Split into validator and function?
 */
final class Validator
{
    use TextTrait, StringTrait;

    /**
     * The messages from the current check
     *
     * @var array
     */
    private $msg = [];

    /**
     * Storage for loaded rule objects
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
     * @param string|array $rules One or more rules
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
            // Parameters can be of type array where the elements are used as function parameters in the .. they are set.
            if (is_array($rule)) {

                // Get the functionname
                $rule_name = $this->camelizeString($rule[0]);

                // Parameters set?
                if (isset($rule[1])) {
                    $args = ! is_array($rule[1]) ? [
                        $rule[1]
                    ] : $rule[1];
                }
                else {
                    $args = [];
                }

                // Custom error message
                if (isset($rule[2])) {
                    $custom_message = $rule[2];
                }
            }
            else {
                $rule_name = $this->camelizeString($rule);
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

                // If no error message is set, use the default validato error
                if (empty($msg)) {
                   $this->msg[] = isset($custom_message) ? $this->txt($custom_message) : $this->txt('validator_error');
                }

                $this->msg[] = $msg;
            }
        }

        return $this->msg;
    }

    /**
     * Returns the last validation result.
     *
     * @return bool
     */
    public function isValid()
    {
        return empty($this->msg);
    }

    /**
     * Returns the last validation msg.
     *
     * @return array
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * Creates and returns a rule object.
     *
     * @param string $rule_name
     *
     * @return RuleAbstract
     */
    public function &createRule($rule_name)
    {
        // Rules are singletons
        if (! array_key_exists($rule_name, $this->rules)) {
            $rule_class = '\Core\Lib\Data\Validator\Rules\\' . $rule_name . 'Rule';
            $this->rules[$rule_name] = $this->di->instance($rule_class, 'core.data.validator');
        } else {

            // Reset existing rules
            $this->rules[$rule_name]->reset();

        }


        return $this->rules[$rule_name];
    }
}
