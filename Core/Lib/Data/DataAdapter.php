<?php
namespace Core\Lib\Data;

/**
 *
 * @author Michael
 *
 */
class DataAdapter implements \IteratorAggregate
{

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
	private $container = false;

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
	 * @throws \InvalidArgumentException
	 */
	public function __construct($type, array $arguments = [])
	{
		if (! array_key_exists($type, self::$adapters)) {
			Throw new \InvalidArgumentException('There is no data adapter of type "' . $type . '" registered');
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
		return call_user_func_array(array($this->adapter, $name), $arguments);
	}

	public function setContainer(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Returns a clone of the registered data container
	 *
	 * @return \Core\Lib\Data\Container
	 */
	public function getContainer()
	{
		return clone $this->container;
	}

	/**
	 * Maps a new data apdapter class.
	 *
	 * @param string $name Unique name of adapter
	 * @param string $class Class to map as adapter
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @return \Core\Lib\Data\DataAdapter
	 */
	public function mapAdapter($name, $class)
	{
		if (array_key_exists($name, self::$adapters)) {
			Throw new \InvalidArgumentException('There is already a data adapter type with name "' . $name . '" registered');
		}

		if (in_array($class, self::$adapters)) {
			Throw new \InvalidArgumentException('There is already a data adapter with class "' . $class . '" registered');
		}

		self::$adapters[$name] = $class;

		return $this;
	}

	/**
	 * Sets data property as container.
	 *
	 * @param Container $container
	 *
	 * @return \Core\Lib\Data\DataAdapter
	 */
	public function setData($data)
	{
		$container = $this->getContainer();
		$container->fill($data);
		$this->data = $container;

		return $this;
	}

	/**
	 * Set an array of data as list of containers to data property.
	 *
	 * @var array $dataset
	 *
	 * @return \Core\Lib\Data\DataAdapter
	 */
	public function setDataset(array $dataset)
	{
		foreach ($dataset as $data) {
			$container = $this->getContainer();
			$container->fill($data);
			$this->data[] = $container;
		}

		return $this;
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
}
