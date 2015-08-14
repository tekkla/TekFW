<?php
namespace Core\Lib\Amvc;

use Core\Lib\Data\Adapter\Db\Connection;
use Core\Lib\Data\DataAdapter;
use Core\Lib\Traits\SerializeTrait;
use Core\Lib\Traits\ArrayTrait;
use Core\Lib\Traits\UrlTrait;
use Core\Lib\Traits\ConvertTrait;
use Core\Lib\Data\Adapter\Database;
use Core\Lib\Data\Vars;

/**
 * Model class
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015 by author
 * @license MIT
 */
class Model extends MvcAbstract implements \ArrayAccess
{

    use SerializeTrait;
    use ArrayTrait;
    use UrlTrait;
    use ConvertTrait {
        SerializeTrait::isSerialized insteadof ConvertTrait;
    }

    /**
     * MVC component type
     *
     * @var string
     */
    protected $type = 'Model';

    protected $data = false;

    /**
     *
     * @var \Core\Lib\Data\Vars
     */
    protected $vars;

    /**
     * Constructor
     */
    final public function __construct($name, App $app, Vars $vars)
    {
        // Set Properties
        $this->name = $name;
        $this->app = $app;
        $this->vars = $vars;
    }

    /**
     * Access to the apps config.
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
     * There is a little difference in using this method than the long term. Not setting a model name
     * means, that you get a new instance of the currently used model.
     *
     * @param string $model_name Optional: When not set the name of the current model will be used
     *
     * @return Model
     */
    final public function getModel($model_name = null)
    {
        if (empty($model_name)) {
            $model_name = $this->getName();
        }

        return $this->app->getModel($model_name);
    }

    /**
     * Creates an app related container
     *
     * @param string $container_name Optional: Name of the container to load. When no name is given the name of the current model will be used.
     * @param bool $auto_init Optional: Autoinit uses the requested action to fill the container with according fields by calling the same called method of container.
     *
     * @return \Core\Lib\Data\Container
     */
    final public function getContainer($container_name = null, $auto_init = true)
    {
        if (empty($container_name)) {
            $container_name = $this->getName();
        }

        return $this->app->getContainer($container_name, $auto_init);
    }

    /**
     * Creates an generic container object.
     *
     * @return \Core\Lib\Data\Container
     */
    final public function getGenericContainer($fields = [])
    {
        $container = $this->di->get('core.data.container');

        if (!empty($fields)) {
            $container->parseFields($fields);
        }

        return $container;
    }

    /**
     * Creates a database Dataadapter object
     *
     * Uses default db DataAdapter when no Connection object is set.
     *
     * @param Connection $conn Optional Connection object
     * @param string $prefix Optional table prefix.
     * @param array $fields Optional field definition list to be used as container scheme
     *
     * @return Database
     */
    final protected function getDbAdapter(Connection $conn = null, $prefix = '', array $fields = [])
    {
        if ($conn === null) {
            $adapter = $this->di->get('db.default');

            if ($prefix) {
                $adapter->setPrefix($prefix);
            }
        }
        else {

            if (! $prefix) {
                $prefix = $this->di->get('db.default.prefix');
            }

            $adapter = new DataAdapter('db', [
                'conn' => $conn,
                'prefix' => $prefix
            ]);
        }

        if ($fields) {
            $adapter->createContainer($fields);
        }

        return $adapter;
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
