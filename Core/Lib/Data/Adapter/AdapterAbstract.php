<?php
namespace Core\Lib\Data\Adapter;

use Core\Lib\Data\DataAdapter;

/**
 * DataAdapert abstract class
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2015 by author
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
