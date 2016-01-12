<?php
namespace Core\Lib\Amvc;

use Core\Lib\Traits\SerializeTrait;
use Core\Lib\Traits\ArrayTrait;
use Core\Lib\Traits\UrlTrait;
use Core\Lib\Data\Connectors\Db\Db;
use Core\Lib\Data\Connectors\Db\Connection;
use Core\Lib\Data\Container;
use Core\Lib\Errors\Exceptions\InvalidArgumentException;
;

/**
 * Model.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Model extends MvcAbstract implements \ArrayAccess
{

    use SerializeTrait;
    use ArrayTrait;
    use UrlTrait;

    /**
     * MVC component type
     *
     * @var string
     */
    protected $type = 'Model';

    protected $data = false;

    /**
     * Constructor
     *
     * @param unknown $name
     * @param App $app
     * @param Vars $vars
     */
    final public function __construct($name, App $app)
    {
        // Set Properties
        $this->name = $name;
        $this->app = $app;
    }

    /**
     * Access to the apps config.
     *
     * Without any paramter set this method returns the complete config.
     * With only key set, it returns the value associated with it.
     * Set key and value, and the config will be updated.
     *
     * @param string $key
     * @param string $val
     *
     * @return mixed
     */
    final public function cfg($key = null, $val = null)
    {
        return $this->app->cfg($key, $val);
    }

    /**
     * Wrapper function for $this->appgetModel($model_name).
     *
     * There is a little difference in using this method than the long term. Not setting a model name
     * means, that you get a new instance of the currently used model.
     *
     * @param string $model_name
     *            Optional: When not set the name of the current model will be used
     *
     * @return Model
     */
    final public function getModel($model_name = '')
    {
        if (empty($model_name)) {
            $model_name = $this->getName();
        }

        return $this->app->getModel($model_name);
    }

    /**
     * Creates an app related container
     *
     * @param string $container_name
     *            Optional: Name of the container to load. When no name is given the name of the current model will be used.
     * @param bool $auto_init
     *            Optional: Autoinit uses the requested action to fill the container with according fields by calling the same called method of container.
     *
     * @return \Core\Lib\Data\Container
     */
    final public function getContainer($container_name = '')
    {
        if (empty($container_name)) {
            $container_name = $this->getName();
        }

        return $this->app->getContainer($container_name);
    }

    /**
     * Creates an generic container object.
     *
     * @return \Core\Lib\Data\Container
     */
    final public function getGenericContainer($fields = [])
    {
        $container = new Container();

        if (! empty($fields)) {
            $container->parseFields($fields);
        }

        return $container;
    }

    /**
     * Creates a database connector driven DataAdapter object
     *
     * Uses default db DataAdapter when no Connection object is set.
     *
     * @param Connection $conn
     *            Optional Connection object
     * @param string $prefix
     *            Optional table prefix.
     * @param array $fields
     *            Optional field definition list to be used as container scheme
     *
     * @return Db
     */
    final protected function getDbConnector($resource_name = 'db.default', $prefix = '', $fields = [])
    {
        if (! $this->di->exists($resource_name)) {
            Throw new InvalidArgumentException(sprintf('A database service with name "%s" ist not registered', $resource_name));
        }

        /* @var $db Db */
        $db = $this->di->get($resource_name);

        if ($prefix) {
            $db->setPrefix($prefix);
        }

        if ($fields !== false) {

            // Try to get a container object
            $container = empty($fields) ? $this->getContainer() : $this->getGenericContainer($fields);

            // Pass it to the DataAdapter
            $db->setContainer($container);
        }

        return $db;
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        if (! is_null($offset)) {
            $this->data[$offset] = $value;
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * (non-PHPdoc)
     *
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
}
