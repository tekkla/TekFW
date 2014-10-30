<?php
namespace Core\Lib\Data\Adapter\Db;

/**
 *
 * @author Michael
 *
 */
class QueryBuilder
{

	use \Core\Lib\Traits\ArrayTrait;
	use \Core\Lib\Traits\ConvertTrait;

	private $method;

	private $tbl;

	private $alias = '';

	private $fields = [];

	private $join = [];

	private $filter = '';

	private $params = [];

	private $order = '';

	private $group_by = '';

	private $having = '';

	/**
	 *
	 * @var array
	 */
	private $limit = [];

	/**
	 * Constructor
	 *
	 * @param array $definition Optional QueryDefinition
	 */
	public function __construct(array $definition = [])
	{
		if ($definition) {
			$this->processQueryDefinition($definition);
		}
	}

	/**
	 * Select fields
	 *
	 * @return \Core\Lib\Data\Adapter\Db\QueryBuilder
	 */
	public function Select()
	{
		$this->method = 'SELECT';

		$this->fields = func_get_args();

		return $this;
	}

	public function SelectDistinct()
	{
		$this->method = 'SELECT DISTINCT';

		$this->fields = func_get_args();

		return $this;
	}

	public function InsertInto()
	{
		$this->method = 'INSERT INTO';

		$this->fields = func_get_args();

		return $this;
	}

	/**
	 * From statement
	 *
	 * @param string $tbl Table name
	 * @param string $alias Optional: Table alias
	 *
	 * @return \Core\Lib\Data\Adapter\Db\QueryBuilder
	 */
	public function From($tbl, $alias = '')
	{
		$this->tbl = $tbl;

		if ($alias) {
			$this->alias = $alias;
		}

		return $this;
	}

	/**
	 * Filter statement
	 *
	 * @param string $filter Filterstring
	 * @param array $params Optional: Paramenter list
	 *
	 * @return \Core\Lib\Data\Adapter\Db\QueryBuilder
	 */
	public function Where($filter, $params = [])
	{
		$this->filter;
		$this->params = $params;

		return $this;
	}

	/**
	 * Order statement
	 *
	 * @param string $order Orderstring
	 *
	 * @return \Core\Lib\Data\Adapter\Db\QueryBuilder
	 */
	public function Order($order)
	{
		$this->order = $order;

		return $this;
	}

	/**
	 * Join statement
	 *
	 * @param string $tbl Table name of table to join
	 * @param string $as Alias of join table
	 * @param string $by How to join
	 * @param string $condition Join condition
	 *
	 * @return \Core\Lib\Data\Adapter\Db\QueryBuilder
	 */
	public function Join($tbl, $as, $by, $condition)
	{
		$this->join[] = [
			'tbl' => $tbl,
			'as' => $as,
			'by' => $by,
			'cond' => $condition
		];

		return $this;
	}

	/**
	 * GroupBy statement.
	 *
	 * @param string|array field name or list of field names as array
	 *
	 * @return \Core\Lib\Data\Adapter\Db\QueryBuilder
	 */
	public function GroupBy($field)
	{
		if (is_array($field)) {
			$val = implode(', ', $field);
		}

		$this->group_by = $val;
		return $this;
	}

	public function Having()
	{}

	/**
	 * Limit statement.
	 *
	 * @param int $lower Lower limit
	 * @param int $upper Optional: Upper limit
	 *
	 * @return \Core\Lib\Data\Adapter\Db\QueryBuilder
	 */
	public function Limit($lower, $upper = null)
	{
		$this->limit['lower'] = (int) $lower;

		if (isset($upper)) {
			$this->limit['upper'] = (int) $upper;
		}

		return $this;
	}

	/**
	 * Buildss the sql string for select queries.
	 *
	 * @return string Sql string
	 */
	public function build()
	{
		$params = [];

		$join = '';
		$filter = '';
		$order = '';
		$limit = '';

		// Create the fieldprefix. If given as alias use this, otherwise we use the tablename
		$field_prefifx = $this->alias ? $this->alias : '{db_prefix}' . $this->tbl;

		// Biuld joins
		if ($this->join) {
			$tmp = [];

			foreach ($this->join as $def) {
				$tmp[] = ' ' . $def['by'] . ' JOIN ' . '{db_prefix}' . (isset($def['as']) ? $def['tbl'] . ' AS ' . $def['as'] : $def['join']) . ' ON (' . $def['cond'] . ')';
			}
		}

		$join = isset($tmp) ? implode(' ', $tmp) : '';

		// Create fieldlist
		if ($this->fields) {

			// Add `` to some field names as reaction to those stupid developers who chose systemnames as fieldnames
			foreach ($this->fields as $key_field => $field) {

				$fields_to_quote = [
					'date',
					'time'
				];

				if (in_array($field, $fields_to_quote)) {
					$field = '`' . $field . '`';
				}

				// Extend fieldname either by table alias or name when no dot as alias/table indicator is found.
				#if (strpos($field, '.') === false) {
				#	$field .= ($this->alias ? $this->alias : $this->tbl) . '.' . $field;
				#}

				$this->fields[$key_field] = $field;
			}

			$fieldlist = implode(', ', $this->fields);
		}
		else {
			$fieldlist = ($this->alias ? $this->alias : $this->tbl) . '.*';
		}

		// Create filterstatement
		$filter = $this->filter ? ' WHERE ' . $this->filter : '';

		// Create group by statement
		$group_by = $this->group_by ? ' GROUP BY ' . $this->group_by : '';

		// Create having statement
		$having = $this->having ? ' HAVING ' . $this->having : '';

		// Create order statement
		$order = $this->order ? ' ORDER BY ' . $this->order : '';

		// Create limit statement
		if ($this->limit) {

			$limit = ' LIMIT ';

			if (isset($this->limit['lower'])) {
				$limit .= $this->limit['lower'];
			}

			if (isset($this->limit['lower']) && isset($this->limit['upper'])) {
				$limit .= ',' . $this->limit['upper'];
			}
		}

		// We need a string for the table. if there is an alias, we have to set it
		$tbl = $this->alias ? $this->tbl . ' AS ' . $this->alias : $this->tbl;

		// Create sql statement by joining all parts from above
		$this->sql = $this->method . ' ' . $fieldlist . ' FROM {db_prefix}' . $tbl . $join . $filter . $group_by . $having . $order . $limit;

		return $this->sql;
	}

	private function processQueryDefinition($def)
	{
		// Use set query type or use 'row' as default
		$this->method = isset($def['method']) ? $def['method'] : 'SELECT';

		if (!isset($def['tbl'])) {
			Throw new \RuntimeException('Missing table name. Provide tablename by setting "tbl" element in your query definition');
		}
		else {
			$this->tbl = $def['tbl'];
		}

		if (isset($def['alias'])) {
			$this->alias = $def['alias'];
		}

		// Set fields
		if (isset($def['field'])) {
			$this->fields = is_array($def['field']) ? $def['field'] : (array) $def['field'];
		}
		else {
			$this->fields = (array) '*';
		}

		// Set filter
		$this->filter = isset($def['filter']) ? $def['filter'] : '';

		// Set params
		if (isset($def['params'])) {
			$this->setParameter($def['params']);
		}

		// Set joins
		if (isset($def['join']) && is_array($def['join'])) {

			foreach ($def['join'] as $join) {

				if ($this->isAssoc($join)) {
					$this->join[] = [
						'tbl' => $join['tbl'],
						'as' => $join['as'],
						'by' => $join['by'],
						'cond' => $join['condition']
					];
				}
				else {
					$this->join[] = [
						'tbl' => $join[0],
						'as' => $join[1],
						'by' => $join[2],
						'cond' => $join[3]
					];
				}
			}
		}

		// Do we have an order statement?
		if (isset($def['order'])) {
			$this->order = $def['order'];
		}

		// Limit to be set?
		if (isset($def['limit'])) {
			// Single int value as limit?
			if (is_int($def['limit'])) {
				$this->limit['lower'] = $def['limit'];
			}

			// Array but only one value`?
			elseif (is_array($def['limit']) && count($def['limit']) == 1) {
				$this->limit['lower'] = (int) $def['limit'][0];
			}

			// Array and two values?
			elseif (is_array($def['limit']) && count($def['limit']) == 2) {
				$this->limit['lower'] = (int) $def['limit'][0];
				$this->limit['upper'] = (int) $def['limit'][1];
			}
		}

		return $this;
	}

	/**
	 * Adds one parameter in form of key and value or a list of parameters as assoc array by resetting existing parameters.
	 * Setting an array as $arg1 and leaving $arg2 empty means to add an assoc array of paramters
	 * Setting $arg1 and $arg2 means to set on parameter by name and value.
	 *
	 * @var string array String with parametername or list of parameters of type assoc array
	 * @var string $arg2 Needs only to be set when seting on paramters by name and value.
	 * @var bool $reset Optional: Set this to true when you want to reset already existing parameters
	 *
	 * @throws Error
	 *
	 * @return \Core\Lib\Url
	 */
	function setParameter($arg1, $arg2 = null)
	{
		if ($arg2 === null && (is_array($arg1) || is_object($arg1))) {
			foreach ($arg1 as $key => $val) {
				$this->params[$key] = $this->convertObjectToArray($val);
			}
		}

		if (isset($arg2)) {
			$this->params[$arg1] = $this->convertObjectToArray($arg2);
		}

		return $this;
	}

	/**
	 * Returns all parameters. On no parameters it returns an empty array.
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}
}
