<?php
namespace Core\Lib\Data\Adapter\Db;

use Core\Lib\Data\Adapter\Database;

/**
 *
 * @author Michael
 *
 */
abstract class TableInfoAbstract
{

	/**
	 * DB Service
	 *
	 * @var Database
	 */
	protected $db;

	/**
	 * Columns list
	 *
	 * @var array
	 */
	private $columns = [];

	/**
	 * Indexlist
	 *
	 * @var array
	 */
	private $indexes = [];

	/**
	 * Constructor
	 *
	 * @param Database $db
	 */
	public function __construct(Database $db, $tbl)
	{
		$this->db = $db;
	}

	/**
	 * Adds a column to columnslist
	 *
	 * @param string $name Name of field
	 * @param bool $null Null allowed
	 * @param number|string $default Default value
	 * @param string $type Fieldtype
	 * @param number $size Fieldsize
	 * @param bool $auto Autoincrement
	 */
	protected function addColumn($name, $null, $default, $type, $size, $auto, $unsigned)
	{
		$this->columns[$name] = [
			'name' => $name,
			'null' => $null,
			'default' => $default,
			'type' => $type,
			'size' => $size,
			'auto' => $auto,
			'unsigned' => $unsigned
		];
	}

	/**
	 * Adds an index to the indexlist.
	 *
	 * @param string $name
	 * @param string $type
	 * @param string $column
	 */
	protected function addIndex($name, $type, $column)
	{
		if (isset($this->indexes[$name])) {
			$this->indexes[$name]['columns'][] = $column;
		}
		else {
			$this->indexes[$name] = [
				'name' => $name,
				'type' => $type,
				'columns' => [
					$column
				]
			];
		}
	}

	/**
	 * Return column information for a table.
	 *
	 * @param string $tbl The name of the table to get column info for
	 * @param bool $detail Whether or not to return detailed info. If true, returns the column info.
	 *        If false, just returns the column names.
	 *
	 * @return array An array of column names or detailed column info, depending on $detail
	 */
	abstract public function loadColumns($tbl);

	/**
	 * Get index information.
	 *
	 * @param string $tble The name of the table to get indexes for
	 * @param bool $detail Whether or not to return detailed info.
	 *
	 * @return array An array of index names or a detailed array of index info, depending on $detail
	 */
	abstract public function loadIndexes($tbl);

	/**
	 * Returns columnslist
	 *
	 * @return array
	 */
	public function getColumns()
	{
		return $this->columns;
	}

	/**
	 * Returns indexlist
	 *
	 * @return array
	 */
	public function getIndexes()
	{
		return $this->indexes;
	}
}
