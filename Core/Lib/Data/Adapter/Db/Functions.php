<?php
namespace Core\Lib\Data\Db;

/**
 *
 * @author Michael
 *
 */
class Functions
{

	/**
	 * Queries DB an returns the first requested colums as array
	 * Perfect for queries wher you only want to get keys
	 *
	 * @param string $sql
	 *
	 * @return array $arr
	 */
	public function getKeys($sql, $params = [], $column_number = 0, $param_mode = 0)
	{
		$this->query($sql);

		foreach ($params as $params => &$val) {
			$bind_func = $param_mode == 1 ? 'bindParam' : 'bindValue';
			$this->{$bind_func} = [
				$param,
				$val
			];
		}

		$this->execute();

		return $this->stmt->fetchColumn($column_number);
	}

	/**
	 * Queries the DB with the paramter sql string and returns
	 * an array where the primarykey of the query represents one key
	 * of the array and each of the other requested colums the
	 * elememtkeys and values represent.
	 *
	 * @param string $sql
	 *
	 * @return array $arr
	 *
	 *         IMPORTANT: YOU HAVE TO PUT YOUR PRIMARY KEY IN THE FIRST
	 *         POSITION OF YOUR SQL STRING. OTHERWISE THE OUTPUT ARRAY WON'T
	 *         BE CORRECT!
	 *
	 *         Example SQL: (in our table are three rows)
	 *         <cod
	 *         "SELECT KeyColumn, Column1, Columns2 FROM table"
	 *         </cod
	 *
	 *         will result in
	 *         <cod
	 *         array[1st valKeyColumn] = array (
	 *         [0] = valColumn1
	 *         [1] => valColumn2
	 *         )
	 *         array[2nd valKeyColumn] = array (
	 *         [0] = valColumn1
	 *         [1] => valColumn2
	 *         )
	 *         array[3rd valKeyColumn] = array (
	 *         [0] = valColumn1
	 *         [1] => valColumn2
	 *         )
	 *         </cod
	 */
	public function getAll($sql, $params = [], $serialized = [], $key_column = 0)
	{
		$stmt = $this->query($sql, $params);

		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

			$record = (object) $row;

			foreach ($serialized as $col_to_unserialize) {
				$record{$col_to_unserialize} = $this->checkSerialized($record{$col_to_unserialize});
			}

			// Get the index
			$cols = array_keys($row);

			$this->{$row[$cols[$key_column]]} = $record;
		}

		return $this->getData();
	}

	/**
	 * Queries the DB and returns one row in form of an assoc array.
	 * Each value is accesible by it's name in the sql string. Only the FIRST row of a result will be processed.
	 * All other rows of a result will be skipped.
	 *
	 * This method is ideal for databaserequests where you only want
	 * to retreive on row.
	 *
	 * @param string $sql
	 *
	 * @return array $arr
	 *
	 *         Input SQL String:
	 *         <cod
	 *         SELECT Column1, Column2, Column2 FROM table WHERE ID=1
	 *         </cod
	 *         <cod
	 *         SELECT Column1, Column2, Column2 FROM table ORDER BY Column1 DESC LIMIT 1
	 *         </cod *
	 *         Output Array:
	 *         <cod
	 *         array (
	 *         'colName0' => Value of Column1
	 *         'colName1' => Value of Column2
	 *         'colName2' => Value of Column3
	 *         )
	 *         </cod
	 */
	public function getRow($sql, $params = [], $serialized = [])
	{
		$row = $this->query($sql, $params)->fetch(\PDO::FETCH_OBJ);

		foreach ($serialized as $col_to_unserialize) {
			$row{$col_to_unserialize} = $this->checkSerialized($row{$col_to_unserialize});
		}

		return $row;
	}

	/**
	 * Queries the DB and returns result row in form of an array with an automated numeric index.
	 *
	 * This method is ideal for databaserequests where you only want
	 * to retreive on row.
	 *
	 * @param string $sql
	 *
	 * @return array $arr
	 *
	 *         Input SQL String:
	 *         <cod
	 *         SELECT Column1, Column2, Column2 FROM table WHERE ID=1
	 *         </cod
	 *         Output Array:
	 *         <cod
	 *         0 => array (
	 *         '0' => Value of Column1
	 *         '1' => Value of Column2
	 *         '2' => Value of Column3
	 *         )
	 *         </cod
	 *
	 * @deprecated
	 *
	 * @todo Really needed anymore?
	 *       public function getConfig($sql, $params = [), $serialized = [))
	 *       {
	 *       $res = $this->query($sql, $params);
	 *
	 *       if ($this->numRows($res) 0)
	 *       $this->data = new \stdClass();
	 *
	 *       while ( $row = $this->fetchRow($res) )
	 *       $this->data->{$row[0]}{$row[1]} = in_array($row[1], $serialized) ? $this->checkSerialized($row[2]) : $row[2];
	 *
	 *       $this->freeResult($res);
	 *
	 *       return $this->data;
	 *       }
	 */

	/**
	 * Queries the db and returns the values of the first column as array.
	 * The first column represents the elementkey. The second the elementvalue.
	 *
	 * @param string $sql - ignores all requested columns except the first and second
	 * @param array $params - possible filter parameter
	 *
	 * @return array - data of the requested column
	 */
	public function getTwoCols($sql, $params = [], $serialized = [])
	{
		$stmt = $this->query($sql, $params);

		$data = [];

		while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
			$data[$row[0]] = $row[1];
		}

		return $data;
	}

	/**
	 * Queries the db and returns only one value.
	 * Ideal for SELECT Count(*) or similiar requests.
	 *
	 * @param string $sql - ignores all requested columns except the first and second
	 * @param array $params - possible filter parameter
	 *
	 * @return mixed $val - requested value
	 */
	public function getOneValue($sql, $params = [], $column = 0)
	{
		$value = $this->query($sql, $params)->fetchColumn($column);

		if ($value) {
			$value = $this->checkSerialized($value);
		}

		return $value;
	}

	/**
	 * Checks argument for being a serialized value which will be returnd unserialized.
	 *
	 * @param string $val
	 *
	 * @return string|Ambigous
	 */
	private function checkSerialized($val = null)
	{
		// Return val when it is not a string or is empty
		if (! is_string($val) || empty($val)) {
			return $val;
		}

		return $this->isSerialized($val) ? unserialize($val) : $val;
	}
}

?>