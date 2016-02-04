<?php
namespace Core\Lib\Amvc;

// DataLibs
use Core\Lib\Data\Connectors\Db\Db;
use Core\Lib\Data\Container\Container;

// Traits
use Core\Lib\Traits\ArrayTrait;
use Core\Lib\Router\UrlTrait;
use Core\Lib\Cfg\CfgTrait;

/**
 * Model.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Model extends MvcAbstract
{
    use ArrayTrait;
    use UrlTrait;
    use CfgTrait;

    /**
     * MVC component type
     *
     * @var string
     */
    protected $type = 'Model';

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
     *            Optional: Name of the container to load. When no name is given the name of the current model will be
     *            used.
     * @param bool $auto_init
     *            Optional: Autoinit uses the requested action to fill the container with according fields by calling
     *            the same called method of container.
     *
     * @return \Core\Lib\Data\Container\Container
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
     * @return \Core\Lib\Data\Container\Container
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
     * Creates a database connector
     *
     * @param string $resource_name
     *            Name of the registered db factory
     * @param string $prefix
     *            Optional table prefix.
     * @param array $fields
     *            Optional field definition list to be used as containerscheme
     *
     * @return Db
     */
    final protected function getDbConnector($resource_name = 'db.default', $prefix = '', $fields = [])
    {
        if (! $this->di->exists($resource_name)) {
            Throw new ModelException(sprintf('A database service with name "%s" ist not registered', $resource_name));
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
}
