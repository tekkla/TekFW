<?php
namespace Core\Validator;

/**
 * RuleInterface.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
interface RuleInterface
{

    /**
     * Constructor
     *
     * @param mixed $value
     *            Value to validate
     */
    public function __construct(Validator $validator);

    /**
     * Sets value to check
     *
     * @param mixed $value
     */
    public function setValue($value);

    /**
     * Execute rule on empty value?
     *
     * @return boolean
     */
    public function getExecuteOnEmpty();

    /**
     * Checks for empty txt property and returns result as validation result.
     *
     * @return boolean
     */
    public function isValid();

    /**
     * Returns the stored message.
     *
     * @return string
     */
    public function getMsg();

    /**
     * Resets rule message
     *
     * Will be called on rule creation process in validator object.No need to call it manually.
     */
    public function reset();

    /**
     * Execute validation
     */
    public function execute();
}
