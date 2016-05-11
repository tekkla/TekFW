<?php
namespace Core\Validator;

use Core\Language\Text;
use function Core\stringCamelize;

/**
 * Validator.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
final class Validator
{

    /**
     *
     * @var array
     */
    private $result = [];

    /**
     *
     * @var array
     */
    private $rules = [];

    /**
     *
     * @var Text
     */
    private $text;

    /**
     *
     * @param Text $text
     */
    public function injectTextAdapter(Text $text)
    {
        $this->text = $text;
    }

    /**
     * Validates a value against the wanted rules
     *
     * Returns an empty array when all rules where checked succesfully.
     *
     * @param mixed $value
     *            The value to validate
     * @param string|array $rules
     *            One or more rules
     */
    public function validate($value, array $rules)
    {
        $this->result = [];

        // Our current value (trimmed)
        $value = trim($value);

        // Validate each rule against the
        foreach ($rules as $rule) {

            // Array type rules are for checks where the func needs one or more parameter
            // So $rule[0] is the func name and $rule[1] the parameter.
            // Parameters can be of type array where the elements are used as function parameters in the .. they are
            // set.
            if (is_array($rule)) {

                // Get the functionname
                $rule_name = stringCamelize($rule[0]);

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
                $rule_name = stringCamelize($rule);
                $args = [];
                unset($custom_message);
            }

            // Call rule creation process to make sure rule exists before starting further actions.
            /* @var $rule \Core\Validator\Rules\RuleAbstract */
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

            // Is the validation result negative eg false?
            if ($rule->isValid() === false) {

                // Get msg from rule
                $msg = $rule->getMsg();

                // If no error message is set, use the default validator error
                if (empty($msg)) {
                    $msg = isset($custom_message) ? $custom_message : 'validator.error';
                }

                if (isset($this->text)) {

                    if (is_array($msg)) {
                        $text = array_shift($msg);
                        $msg = vsprintf($this->text->get($text), $msg);
                    }
                    else {
                        $msg = $this->text->get($msg);
                    }
                }

                $this->result[] = $msg;
            }
        }
    }

    /**
     * Returns the last validation result
     *
     * @return bool
     */
    public function isValid()
    {
        return empty($this->result);
    }

    /**
     * Returns the last validation msg
     *
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Creates and returns a singleton rule object
     *
     * @param string $rule_name
     *            Name of the rule
     *
     * @return RuleAbstract
     */
    public function &createRule($rule_name)
    {
        // Rules have to be singletons
        if (empty($this->rules[$rule_name])) {

            // Without a leading \ in the rulename it is assumened that we use a Core FW builtin rule
            // otherwise the $rule_name points to a class somewhere outsite of the frasmworks default rules.
            $rule_class = strpos($rule_name, '\\') == 0 ? __NAMESPACE__ . '\Rules\\' . $rule_name . 'Rule' : $rule_name;

            // Create the rule obejct instance
            $rule_object = new $rule_class($this);

            // The rule object must be a child of RuleAbstract!
            if (! $rule_object instanceof RuleInterface) {
                Throw new ValidatorException('Validator rules MUST a either implement the RuleInterface or be an instance of AbtractRule');
            }

            // Add rule to the rules stack
            $this->rules[$rule_name] = $rule_object;
        }
        else {

            // Reset existing rules
            $this->rules[$rule_name]->reset();
        }

        return $this->rules[$rule_name];
    }
}
