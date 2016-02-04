<?php
namespace Core\Lib\Data\Connectors;

use Core\Lib\Data\DataAdapter;
use Core\Lib\Data\CallbackInterface;
use Core\Lib\Data\Container\Container;

/**
 * AdapterAbstract.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
abstract class ConnectorAbstract implements CallbackInterface
{

    /**
     *
     * @var DataAdapter
     */
    public $adapter;

    /**
     * Sets container to store dataresult in.
     *
     * @param array|object $container
     */
    public function setContainer(Container $container)
    {
        $this->adapter->setContainer($container);
    }

    /**
     * Injects DataAdapter into adapter class
     *
     * @param DataAdapter $adapter
     */
    final public function injectAdapter(DataAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /*
     * (non-PHPdoc)
     * @see \Core\Lib\Data\CallbackInterface::addCallback()
     */
    public function addCallback($call, array $args = array(), $clear_callbacks_stack = true)
    {
        $this->adapter->addCallback($call, $args, $clear_callbacks_stack);
    }

    /*
     * (non-PHPdoc)
     * @see \Core\Lib\Data\CallbackInterface::addCallbacks()
     */
    public function addCallbacks(array $callbacks = array(), $clear_callbacks_stack = true)
    {
        $this->adapter->addCallbacks($callbacks, $clear_callbacks_stack);
    }

    /*
     * (non-PHPdoc)
     * @see \Core\Lib\Data\CallbackInterface::clearCallbacks()
     */
    public function clearCallbacks()
    {
        $this->adapter->clearCallbacks();
    }
}
