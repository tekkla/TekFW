<?php
namespace Core\Lib\Data\Adapter\Db;

/**
 *
 * @author Michael
 *
 */
class Table
{

	/**
	 *
	 * @var Database
	 */
	private $db;

	/**
	 *
	 * @var string
	 */
	private $tbl;

	/**
	 *
	 * @var string
	 */
	private $prefix;

	/**
	 *
	 * @var array
	 */
	private $columns;

	/**
	 *
	 * @var array
	 */
	private $indexes;

	/**
	 * Constructor
	 *
	 * @param Database $db
	 * @param string $tbl
	 * @param string $prefix
	 */
	public function __construct(Database $db, $tbl, $prefix='')
	{
		$this->db = $db;
		$this->tbl = $tbl;
		$this->prefix = $prefix;

		$this->getTblStructure();
	}

	/**
	 * Get complex table structure.
	 *
	 * @param string $this->tble The name of the table
	 *
	 * @return An array of table structure - the name, the column info from {@link listTblColumns()} and the index info from {@link listTblIndexes()}
	 */
	private function getTblStructure()
	{
		if (strpos($this->tbl, '{db_prefix}') !== 0 || strpos($this->tbl, $this->prefix) !== 0) {
			$this->tbl = $this->prefix . $this->tbl;
		}
		else {
			$this->tbl = str_replace('{db_prefix}', $this->prefix, $this->tbl);
		}

		$driver_class = '\Core\Lib\Data\Db\\' . ucfirst($this->conn->getDriver());

		/* @var $db_subs TableInfoAbstract */
		$db_subs = new $driver_class($this, $this->tbl);

		$db_subs->loadColumns($this->tbl);
		$this->columns = $db_subs->getColumns();

		$db_subs->loadIndexes($this->tbl);
		$this->indexes = $db_subs->getIndexes();
	}

	public function getColumns()
	{
		return $this->columns;
	}

	public function getIndexes()
	{
		return $this->indexes;
	}
}
