<?php
namespace Core\Lib\Data\Adapter;

use Core\Lib\Data\DataAdapter;
/**
 *
 * @author Michael
 *
 */
abstract class AdapterAbstract
{

	/**
	 *
	 * @var DataAdapter
	 */
	protected $adapter;

	public function injectAdapter(DataAdapter $adapter)
	{
		$this->adapter = $adapter;
	}

}

?>