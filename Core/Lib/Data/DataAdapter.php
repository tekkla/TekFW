<?php
namespace Core\Lib\Data;

use Core\Lib\Traits\ArrayTrait;
use Core\Lib\Errors\Exceptions\InvalidArgumentException;
use Core\Lib\Data\Connectors\ConnectorAbstract;

/**
 * DataAdapter.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class DataAdapter implements \IteratorAggregate
{

    use ArrayTrait;

    /**
     *
     * @var string
     */
    private $type;

    /**
     *
     * @var ConnectorAbstract
     */
    private $connector;

    /**
     *
     * @var array
     */
    private $data = [];

    /**
     *
     * @var array
     */
    private $callbacks = [];

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Container\Container::getIterator()
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * Access direct on connector object
     *
     * @param string $name
     * @param array $arguments
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([
            $this->connector,
            $name
        ], $arguments);
    }

    /**
     * Sets data property as container.
     *
     * @param mixed $data
     *
     * @return \Core\Lib\Data\DataAdapter
     */
    public function setData($data, array $scheme = [])
    {
        // We have callbacks to use
        foreach ($this->callbacks as $cb) {

            // Adds data in from of all callback parameters
            array_unshift($cb[1], $data);

            // Call method in callback object with given parameter
            $data = call_user_func_array($cb[0], $cb[1]);
        }

        $this->checkType($data, $scheme);

        $this->data = $data;

        return $this;
    }

    /**
     * Set an array of data to data property.
     *
     * Use this if you want to add a bunch of records to the adapter.
     *
     * @var array $dataset The data to store
     * @param array $scheme
     *            Optional data scheme array
     *
     * @return \Core\Lib\Data\DataAdapter
     */
    public function setDataset(array $dataset, array $scheme = [])
    {
        // Init adapters data array
        $this->data = [];

        foreach ($dataset as $data) {

            // Init skip flag
            $skip = false;

            // Callbacks?
            foreach ($this->callbacks as $cb) {

                if (! isset($cb[1])) {
                    $cb[1] = [];
                }

                // Adds data in from of all callback parameters
                array_unshift($cb[1], $data);

                $data = call_user_func_array($cb[0], $cb[1]);

                // Callback returned boolean false?
                if ($data === false) {

                    // Set skip flag and stop callback
                    $skip = true;
                    break;
                }
            }

            // Drop this data?
            if ($skip) {
                continue;
            }

            $this->checkType($data, $scheme);

            // Use the existing primary field name from scheme when it's available in data
            if (! empty($scheme['primary']) && !empty($data[$scheme['primary']])) {
                $this->data[$data[$scheme['primary']]] = $data;
            }
            else {
                $this->data[] = $data;
            }
        }

        return $this;
    }

    private function checkType(&$data, array $scheme)
    {
        if (empty($scheme) || empty($scheme['fields'])) {
            return;
        }

        // Let's set some types
        foreach ($data as $name => $value) {

            // Data does not exist as field in scheme? Skip it!
            if (empty($scheme['fields'][$name])) {
                continue;
            }

            // copy field defintion for better reading
            $field = $scheme['fields'][$name];

            // Is this field flagged as serialized?
            if (! empty($field['serialize'])) {
                $data[$name] = unserialize($data[$name]);
            }

            // Empty data value but non empty default value in scheme set? Use it!
            if (empty($data[$name]) && ! empty($field['default'])) {
                $data[$name] = $field['default'];
            }

            // TODO Really neccessary? What are the advantages?
            /*
            // Ste type to 'string' when no type is set in scheme
            if (empty($field['type'])) {
                $field['type'] = 'string';
            }

            // Explicit type conversion
            if (! empty($field['type'])) {

                $types = [
                    'boolean',
                    'integer',
                    'float',
                    'string',
                    'array',
                    'object',
                    'null'
                ];

                if (! in_array($field['type'], $types)) {
                    Throw new DataException(sprintf('Type "%s" is not allowed as fieldtype. Allowed types are: %s', $field['type'], implode(', ', $types)));
                }

                settype($data[$name], $field['type']);

            }*/
        }
    }

    /**
     * Returns adapter data.
     *
     * @return multitype:
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Boolean check for data in adapter.
     *
     * @return boolean
     */
    public function hasData()
    {
        return $this->data ? true : false;
    }

    /**
     * Sets one or more callback functions.
     *
     * @param array $callbacks
     *            Array of callbacks to add. Callbacks need at least following index structure:
     *            0 => Closure or [object, method] to call
     *            1 => (optional) Args to pass to call additionally to always provided data.
     * @param boolean $clear_callbacks_stack
     *            (optional) Clears existing callback stack. Default: true
     *
     * @return \Core\Lib\Data\DataAdapter
     */
    public function addCallbacks(array $callbacks = [], $clear_callbacks_stack = true)
    {
        if ($clear_callbacks_stack) {
            $this->clearCallbacks();
        }

        foreach ($callbacks as $cb) {

            // Check for closure or object. If none is found, throw exception
            if (! is_callable($cb[0]) || (is_array($cb[0]) && ! is_object($cb[0][0]))) {
                Throw new InvalidArgumentException('DataAdapter callbacks MUST be either a closure or a valid object.');
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

        return $this;
    }

    /**
     * Adds one callback function.
     *
     * @param closure|array $call
     *            The closure or array with object and method to call.
     * @param array $args
     *            (optional) Arguments to pass additionally to always added data.
     * @param string $clear_callbacks_stack
     *            (optional) Clears existing callback stack. Default: true
     *
     * @return \Core\Lib\Data\DataAdapter
     */
    public function addCallback($call, array $args = [], $clear_callbacks_stack = true)
    {
        // Check for closure or object. If none is found, throw exception
        if (! is_callable($call) || (is_array($call) && ! is_object($call[0]))) {
            Throw new InvalidArgumentException('DataAdapter callbacks MUST be either a closure or a valid object.');
        }

        if ($clear_callbacks_stack) {
            $this->clearCallbacks();
        }

        $this->callbacks[] = [
            $call,
            ! is_array($args) ? (array) $args : $args
        ];

        return $this;
    }

    /**
     * Removes all callback functions.
     *
     * @return \Core\Lib\Data\DataAdapter
     */
    public function clearCallbacks()
    {
        $this->callbacks = [];

        return $this;
    }
}
