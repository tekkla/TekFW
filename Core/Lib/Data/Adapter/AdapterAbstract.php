<?php
namespace Core\Lib\Data\Adapter;

use Core\Lib\Data\DataAdapter;

/**
 * AdapterAbstract.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
abstract class AdapterAbstract
{

    /**
     *
     * @var DataAdapter
     */
    protected $adapter;

    /**
     * Injects DataAdapter into adapter class
     *
     * @param DataAdapter $adapter
     *
     * @return Database|Xml|Json
     */
    final public function injectAdapter(DataAdapter $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }
}
