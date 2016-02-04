<?php
namespace Core\Lib\Data;

/**
 * CallbackInterface.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
interface CallbackInterface
{

    /**
     * Sets one or more callback functions
     *
     * @param array $callbacks
     *            Array of callbacks to add. Callbacks need at least following index structure:
     *            0 => Closure or [object, method] to call
     *            1 => (optional) Args to pass to call additionally to always provided data.
     * @param boolean $clear_callbacks_stack
     *            (optional) Clears existing callback stack. Default: true
     */
    public function addCallbacks(array $callbacks = [], $clear_callbacks_stack = true);

    /**
     * Adds one callback function
     *
     * @param closure|array $call
     *            The closure or array with object and method to call.
     * @param array $args
     *            (optional) Arguments to pass additionally to always added data.
     * @param string $clear_callbacks_stack
     *            (optional) Clears existing callback stack. Default: true
     */
    public function addCallback($call, array $args = [], $clear_callbacks_stack = true);

    /**
     * Removes all callback functions
     */
    public function clearCallbacks();
}

