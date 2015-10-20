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
     * @var mixed
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
     * @return \Core\Lib\Data\Container
     */
    public function getContainer()
    {
        if (! $this->container) {
            Throw new UnexpectedValueException('There is no data container in this DataAdapter.');
        }

        return unserialize(serialize($this->container));
    }

    /**
     * Maps a new data apdapter class.
     *
     * @param string $name Unique name of adapter
     * @param string $class Class to map as adapter
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
        // Callbacks?
        if ($this->callbacks) {

            // We have callbacks to use
            foreach ($this->callbacks as $callback) {

                // Execute every callback registerd
                foreach ($callback[1] as $function) {
                    $data = $callback[0]->$function($data);
                }
            }
        }

        // When data is an assoc array we check here for an existing
        // Container object and fill the container with our data
        if (! $this->arrayIsAssoc($data) || is_array($this->container)) {
            $this->data = $data;
        }
        else {
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
            if ($this->callbacks) {

                // We have callbacks to use
                foreach ($this->callbacks as $callback) {

                    // Execute every callback registerd
                    foreach ($callback[1] as $function) {

                        $data = $callback[0]->$function($data);

                        // Callback returned boolean false?
                        if ($data === false) {

                            // Set skip flag and stop callback
                            $skip = true;
                            break;
                        }
                    }

                    // Skip other callbacks?
                    if ($skip) {
                        break;
                    }
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
            }
            else {
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
     * @param object $object Object the callbaks are calles from
     * @param string|array $callbacks One or more callback functions
     *
     * @return \Core\Lib\Data\DataAdapter
     */
    public function setCallbacks($object, $callbacks = [])
    {
        $this->callbacks[] = [
            $object,
            is_array($callbacks) ? $callbacks : (array) $callbacks
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
