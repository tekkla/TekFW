<?php
namespace Core\Data\Connectors;

use Core\Data\DataAdapter;

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
     * @see \Core\Data\CallbackInterface::addCallback()
     */
    public function addCallback($call, array $args = [], $clear_callbacks_stack = true)
    {
        $this->adapter->addCallback($call, $args, $clear_callbacks_stack);
    }

    /*
     * (non-PHPdoc)
     * @see \Core\Data\CallbackInterface::addCallbacks()
     */
    public function addCallbacks(array $callbacks = [], $clear_callbacks_stack = true)
    {
        $this->adapter->addCallbacks($callbacks, $clear_callbacks_stack);
    }

    /*
     * (non-PHPdoc)
     * @see \Core\Data\CallbackInterface::clearCallbacks()
     */
    public function clearCallbacks()
    {
        $this->adapter->clearCallbacks();
    }
}
