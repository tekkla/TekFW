<?php
namespace Core\Data;

/**
 * AbstractCallbackHandler.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
abstract class AbstractCallbackHandler implements CallbackHandlerInterface
{

    /**
     *
     * @var array
     */
    private $callbacks = [];

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Data\Connectors\CallbackInterface::addCallbacks()
     *
     */
    public function addCallbacks(array $callbacks = [], $clear_callbacks_stack = true)
    {
        if ($clear_callbacks_stack) {
            $this->clearCallbacks();
        }

        foreach ($callbacks as $cb) {

            // Check for closure or object. If none is found, throw exception
            if (! is_callable($cb[0]) || (is_array($cb[0]) && ! is_object($cb[0][0]))) {
                Throw new DataException('DataAdapter callbacks MUST be either a closure or a valid object.');
            }

            // Any callback arguments?
            if (isset($cb[1])) {
                if (! is_array($cb[1])) {
                    $cb[1] = (array) $cb[1];
                }
                $args = $cb[1];
            }
            else {
                $args = [];
            }

            $this->callbacks[] = [
                $cb[0],
                $args
            ];
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Data\Connectors\CallbackInterface::clearCallbacks()
     *
     */
    public function clearCallbacks()
    {
        $this->callbacks = [];
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Data\Connectors\CallbackInterface::addCallback()
     *
     */
    public function addCallback($call, array $args = [], $clear_callbacks_stack = true)
    {
        // // Check for closure or object. If none is found, throw exception
        if (! is_callable($call) || (is_array($call) && ! is_object($call[0]))) {
            Throw new DataException('Connector callbacks MUST be either a closure or a valid object.');
        }

        if ($clear_callbacks_stack) {
            $this->clearCallbacks();
        }

        $this->callbacks[] = [
            $call,
            ! is_array($args) ? (array) $args : $args
        ];
    }

    public function execute(DataObjectInterface $data)
    {

        // We have callbacks to use
        foreach ($this->callbacks as $cb) {

            // Adds data in front of all callback parameters
            array_unshift($cb[1], $data);

            // Call method in callback object with given parameter
            $result = call_user_func_array($cb[0], $cb[1]);

            if ($result === false) {
                return false;
            }
        }
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see Countable::count()
     */
    public function count()
    {
        return count($this->callbacks);
    }
}