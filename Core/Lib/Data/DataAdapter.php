<?php
namespace Core\Lib\Data;

use Core\Lib\Traits\ArrayTrait;
use Core\Lib\Errors\Exceptions\InvalidArgumentException;
use Core\Lib\Errors\Exceptions\UnexpectedValueException;

/**
 * DataAdapter.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
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
     * @var AdapterInterface
     */
    private $adapter;

    /**
     *
     * @var array
     */
    private $data = [];

    /**
     *
     * @var Container
     */
    private $container = [];

    /**
     *
     * @var array
     */
    private $callbacks = [];

    /**
     *
     * @var array
     */
    private static $adapters = [
        'db' => '\Core\Lib\Data\Adapter\Database',
        'xml' => '\Core\Lib\Data\Adapter\Xml',
        'json' => '\Core\Lib\Data\Adapter\Json'
    ];

    /**
     * Constructor
     *
     * @param string $type
     * @param array $arguments
     *
     * @throws InvalidArgumentException
     */
    public function __construct($type, array $arguments = [])
    {
        if (! array_key_exists($type, self::$adapters)) {
            Throw new InvalidArgumentException('There is no data adapter of type "' . $type . '" registered');
        }

        $this->type = $type;

        if (! is_array($arguments)) {
            $arguments = (array) $arguments;
        }

        $this->adapter = new self::$adapters[$this->type]($arguments);
        $this->adapter->injectAdapter($this);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Container::getIterator()
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * Access direct on adapter object
     *
     * @param string $name
     * @param array $arguments
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([
            $this->adapter,
            $name
        ], $arguments);
    }

    /**
     * Sets container to store dataresult in.
     *
     * @param array|object $container
     *
     * @return \Core\Lib\Data\DataAdapter
     */
    public function setContainer($container = [])
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Returns a clone of the registered data container.
     *
     * @throws UnexpectedValueException
     *
     * @return Container
     */
    public function getContainer($generic = true)
    {
        if ($generic == true) {
            return new Container();
        }

        if (! $this->container) {
            Throw new UnexpectedValueException('There is no data container in this DataAdapter.');
        }

        return unserialize(serialize($this->container));
    }

    /**
     * Maps a new data apdapter class.
     *
     * @param string $name
     *            Unique name of adapter
     * @param string $class
     *            Class to map as adapter
     *
     * @throws InvalidArgumentException
     *
     * @return \Core\Lib\Data\DataAdapter
     */
    public function mapAdapter($name, $class)
    {
        if (array_key_exists($name, self::$adapters)) {
            Throw new InvalidArgumentException('There is already a data adapter type with name "' . $name . '" registered');
        }

        if (in_array($class, self::$adapters)) {
            Throw new InvalidArgumentException('There is already a data adapter with class "' . $class . '" registered');
        }

        self::$adapters[$name] = $class;

        return $this;
    }

    /**
     * Sets data property as container.
     *
     * @param mixed $data
     *
     * @return \Core\Lib\Data\DataAdapter
     */
    public function setData($data)
    {
        // We have callbacks to use
        foreach ($this->callbacks as $cb) {

            // Adds data in from of all callback parameters
            array_unshift($cb[1], $data);

            // Call method in callback object with given parameter
            $data = call_user_func_array($cb[0], $cb[1]);
        }

        // When data is an assoc array we check here for an existing
        // Container object and fill the container with our data
        if (! $this->arrayIsAssoc($data) || is_array($this->container)) {
            $this->data = $data;
        } else {
            $this->data = $this->fillContainer($data);
        }

        return $this;
    }

    /**
     * Set an array of data as list of containers to data property.
     *
     * Use this if you want to add a bunch of records to the adapter.
     *
     * @var array $dataset
     *
     * @return \Core\Lib\Data\DataAdapter
     */
    public function setDataset(array $dataset)
    {
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

            // When data is an assoc array we check here for an existing
            // Container object and fill the container with our data
            if (! $this->arrayIsAssoc($data) || is_array($this->container)) {
                $this->data[] = $data;
            } else {
                $this->data[] = $this->fillContainer($data);
            }
        }

        return $this;
    }

    /**
     * Creates a data container from provided data by using a copy of set container.
     *
     * @param array $data
     *
     * @return array|object
     */
    private function fillContainer(array $data)
    {
        $container = unserialize(serialize($this->container));

        foreach ($data as $field => $value) {
            $container[$field] = $value;
        }

        return $container;
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
            } else {
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
            !is_array($args) ? (array) $args : $args
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
