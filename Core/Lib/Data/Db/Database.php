<?php
namespace Core\Lib\Data\Db;

/**
 * SMF db wrapper Class to work as some kind of ORM
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 */
class Database
{
	use\Core\Lib\Traits\SerializeTrait;

	/**
	 * Conversionlist from db fieldtype to smf fieldtypes
	 *
	 * @var array
	 */
	private $conversionlist = [
		'text' => \PDO::PARAM_STR,
		'char' => \PDO::PARAM_STR,
		'int' => \PDO::PARAM_INT,
		'decimal' => \PDO::PARAM_STR,
		'double' => \PDO::PARAM_STR,
		'float' => \PDO::PARAM_STR,
		'numeric' => \PDO::PARAM_STR,
		'date' => \PDO::PARAM_STR,
		'time' => \PDO::PARAM_STR,
		'string' => \PDO::PARAM_STR
	];

	/**
	 * Sql string
	 *
	 * @var string
	 */
	private $sql;

	/**
	 * Table name
	 *
	 * @var string
	 */
	private $tbl = '';

	/**
	 * Connection object
	 *
	 * @var Connection
	 */
	private $conn;

	/**
	 * PDO database handler
	 *
	 * @var \PDO
	 */
	private $dbh = false;

	/**
	 * PDO statement object
	 *
	 * @var \PDOStatement
	 */
	private $stmt = '';

	/**
	 * Table prefix to use
	 *
	 * @var string
	 */
	private $prefix;

	/**
	 * Storage for default database object
	 *
	 * @var unknown
	 */
	private static $default_instance;

	/**
	 * Constructor
	 *
	 * @param Connection $conn
	 * @param string $prefix
	 */
	public function __construct(Connection $conn, $prefix)
	{
		$this->conn = $conn;
		$this->dbh = $this->conn->connect();
		$this->prefix = $prefix;


	}

	/**
	 * Sets table prefix
	 *
	 * @param string $db_prefix
	 *
	 * @return \Core\Lib\Data\Db\Database
	 */
	public function setPrefix($db_prefix)
	{
		$this->prefix = $db_prefix;

		return $this;
	}

	/**
	 * Returns set table prefix
	 *
	 * @return string
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}

	/**
	 * Converts as string => type to smf db type.
	 *
	 * Returns false on failed conversion.
	 *
	 * @param string $type
	 *
	 * @return string|bool
	 */
	public function convertType($type)
	{
		foreach ($this->conversionlist as $to_search => $new_type) {

			if (preg_match('/' . $to_search . '/', $type)) {
				return $new_type;
			}
		}

		return false;
	}

	/**
	 * Converts the table datafields into smf compatible datatypes for usage as parameter in queries
	 *
	 * @param array $fieldlist
	 *
	 * @return array $fieldlist
	 */
	public function convFldTypes($fieldlist)
	{
		foreach ($fieldlist as $field => $type) {

			foreach ($this->conversionlist as $to_search => $new_type) {

				if (preg_match('/' . $to_search . '/', $type)) {
					$fieldlist{$field} = $new_type;
				}
			}
		}

		return $fieldlist;
	}

	private function checkPrefix()
	{
		if (strpos($this->tbl, '{db_prefix}') !== 0 || strpos($this->tbl, $this->prefix) !== 0) {
			$this->tbl = $this->prefix . $this->tbl;
		}
		else {
			$this->tbl = str_replace('{db_prefix}', $this->prefix, $this->tbl);
		}
	}

	/**
	 * Get complex table structure.
	 *
	 * @param string $tble The name of the table
	 *
	 * @return An array of table structure - the name, the column info from {@link listTblColumns()} and the index info from {@link listTblIndexes()}
	 */
	public function getTblStructure($tbl = '')
	{
		$this->tbl = $tbl;

		$this->checkPrefix();

		$driver_class = '\Core\Lib\Data\Db\\' . ucfirst($this->conn->getDriver());

		/* @var $db_subs TableInfoAbstract */
		$db_subs = new $driver_class($this, $this->tbl);

		$db_subs->loadColumns($this->tbl);
		$db_subs->loadIndexes($this->tbl);

		return [
			'name' => $this->tbl,
			'columns' => $db_subs->getColumns(),
			'indexes' => $db_subs->getIndexes()
		];
	}

	/**
	 * This is the db query method.
	 *
	 * @param string $sql
	 * @param optional array $params
	 *
	 * @return \PDOStatement
	 */
	public function query($sql)
	{
		$this->stmt = $this->dbh->prepare(str_replace('{db_prefix}', $this->prefix, $sql));

		return $this->stmt;
	}

	/**
	 * Bind parameter by value to PDO statement
	 *
	 * @param string $param
	 * @param mixed $value
	 * @param string $type Optional
	 *
	 * @return \PDOStatement
	 */
	public function bindValue($param, $value, $type = null)
	{
		if (is_null($type)) {
			switch (true) {
				case is_int($value):
					$type = \PDO::PARAM_INT;
					break;
				case is_bool($value):
					$type = \PDO::PARAM_BOOL;
					break;
				case is_null($value):
					$type = \PDO::PARAM_NULL;
					break;
				default:
					$type = \PDO::PARAM_STR;
			}
		}

		if (is_array($value)) {
			$value = implode(', ', $value);
		}

		$this->stmt->bindValue($param, $value, $type);

		return $this->stmt;
	}

	/**
	 * Bind parameter by param to PDO statement
	 *
	 * @param string $param
	 * @param mixed $value
	 * @param string $type Optional
	 *
	 * @return \PDOStatement
	 */
	public function bindParam($param, &$value, $type = null)
	{
		if (is_null($type)) {

			switch (true) {
				case is_int($value):
					$type = \PDO::PARAM_INT;
					break;
				case is_bool($value):
					$type = \PDO::PARAM_BOOL;
					break;
				case is_null($value):
					$type = \PDO::PARAM_NULL;
					break;
				default:
					$type = \PDO::PARAM_STR;
			}
		}

		$this->stmt->bindParam($param, $value, $type);

		return $this->stmt;
	}

	/**
	 * Executes prepared statement.
	 *
	 * @return boolean
	 */
	public function execute()
	{
		return $this->stmt->execute();
	}

	/**
	 * @see all()
	 * @deprecated
	 */
	public function resultset($fetch_mode = \PDO::FETCH_ASSOC) {
		return $this->all($fetch_mode = \PDO::FETCH_ASSOC);
	}

	/**
	 * Returns all queried data.
	 *
	 * @param int $fetch_mode PDO fetch mode
	 *
	 * @return array
	 */
	public function all($fetch_mode = \PDO::FETCH_ASSOC)
	{
		return $this->stmt->fetchAll($fetch_mode);
	}

	/**
	 * Returns current row of resultset
	 *
	 * @param int $fetch_mode PDO fetch mode
	 *
	 * @return mices
	 */
	public function single($fetch_mode = \PDO::FETCH_ASSOC)
	{
		return $this->stmt->fetch($fetch_mode);
	}

	/**
	 * Returns all rows of specific column in resultset.
	 *
	 * @param number $column Colum to return
	 *
	 * @return array
	 */
	public function column($column = 0)
	{
		return $this->stmt->fetchAll(\PDO::FETCH_COLUMN, $column);
	}

	/**
	 * Returns value of first column in first row
	 *
	 * @return mixed
	 */
	public function value()
	{
		return $this->stmt->fetchColumn();
	}

	/**
	 * Returns number or rows in resultset.
	 *
	 * @return number
	 */
	public function rowCount()
	{
		return $this->stmt->rowCount();
	}

	/**
	 * Returns columncount of resultset
	 *
	 * @return number
	 */
	public function columnCount()
	{
		return $this->stmt->columnCount();
	}

	/**
	 * Returns id of last inserted record
	 *
	 * @return number
	 */
	public function lastInsertId()
	{
		return $this->dbh->lastInsertId();
	}

	/**
	 * Begins a PDO transaction
	 *
	 * @return boolean
	 */
	public function beginTransaction()
	{
		return $this->dbh->beginTransaction();
	}

	/**
	 * Commits PDO transaction
	 *
	 * @return boolean
	 */
	public function endTransaction()
	{
		return $this->dbh->commit();
	}

	/**
	 * Cancels and rolls back PDO transaction
	 *
	 * @return boolean
	 */
	public function cancelTransaction()
	{
		return $this->dbh->rollBack();
	}

	/**
	 * Returns params used by statement
	 *
	 * @return array
	 */
	public function debugDumpParams()
	{
		return $this->stmt->debugDumpParams();
	}

	/**
	 * Nullifies stmd and dbh properties to close connection.
	 *
	 * @return boolean
	 */
	public function close()
	{
		$this->stmt = null;
		$this->dbh = null;

		return true;
	}

	/**
	 *
	 * @param string $method "Insert" or "Replace"
	 * @param string $tbl Full name of the table to insert data. Do not forget {db_prefix}!
	 * @param array $fields Array of the coloums we have data for
	 * @param array $values The value to insert
	 * @param array $keys Array of table keys
	 *
	 * @return integer The id of the last inserted record
	 */
	public function insert($method, $tbl, $fields, $values, $keys)
	{
		// Generate field and parameter list
		$field_list = array_keys($fields);
		$param_list = [];

		foreach ($field_list as $field_name) {
			$param_list[] = ':' . $field_name;
		}

		$stmt = $this->dbh->prepare('INSERT INTO ' . $tbl . ' (' . implode(', ', $field_list) . ') VALUES (' . implode(', ', $param_list) . ')');

		foreach ($fields as $param => &$var) {
			$stmt->bindValue(':' . $param, $var);
		}

		$stmt->execute();

		return $this->dbh->lastInsertId($keys[0]);
	}



	/**
	 * Returns the list of queries done.
	 *
	 * @return multitype:
	 */
	public function getQueryList()
	{
		return self::$queries;
	}

	/**
	 * Returns the number counter of queries done.
	 *
	 * @return number
	 */
	public function getQueryCounter()
	{
		return $this->query_counter;
	}

	/**
	 * Creates a string of named parameters and an array of named parameters => values.
	 *
	 * @param string $param
	 * @param array $values
	 *
	 * @return array
	 */
	public function prepareArrayQuery($param, $values = [])
	{
		$params_names = [];
		$params_val = [];

		foreach ($values as $key => $val) {
			$name = ':' . $param . $key;
			$params_name[] = $name;
			$params_val[$name] = $val;
		}

		return [
			'sql' => implode(', ', $params_name),
			'values' => $params_val
		];
	}

	/**
	 * Returns interpolated sql string with parameters
	 *
	 * @return string
	 */
	public function debugSql($sql, $param=[])
	{
		if ($param) {

			$indexed = $param == array_values($param);

			foreach ($param as $k => $v) {

				if (is_object($v)) {

					if ($v instanceof \DateTime) {
						$v = $v->format('Y-m-d H:i:s');
					}
					else {
						continue;
					}
				}
				elseif (is_string($v)) {
					$v = "'$v'";
				}
				elseif ($v === null) {
					$v = 'NULL';
				}
				elseif (is_array($v)) {
					$v = implode(',', $v);
				}

				if ($indexed) {
					$sql = preg_replace('/\?/', $v, $sql, 1);
				}
				else {
					$sql = str_replace($k, $v, $sql);
				}
			}
		}

		$sql = str_replace('{db_prefix}', $this->prefix, $sql);

		return $sql;
	}
}
