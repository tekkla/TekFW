<?php
namespace Core\Lib\Data\Connectors\Db;

use Core\Lib\Data\Connectors\ConnectorAbstract;
use Core\Lib\Data\Connectors\Db\Connection;
use Core\Lib\Data\Connectors\Db\QueryBuilder;
use Core\Lib\Traits\SerializeTrait;
use Core\Lib\Traits\DebugTrait;
use Core\Lib\Errors\Exceptions\InvalidArgumentException;
use Core\Lib\Data\DataAdapter;

/**
 * Database connector
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Db extends ConnectorAbstract
{
    use SerializeTrait;
    use DebugTrait;

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
    private $sql = '';

    /**
     * Used parameters
     *
     * @var array
     */
    private $params = [];

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

    private static $queries = [];

    private static $query_count = 0;

    /**
     * Constructor
     *
     * @param Connection $conn
     * @param string $prefix
     *
     * @throws InvalidArgumentException
     */
    public function __construct($conn, $prefix)
    {
        $this->conn = $conn;
        $this->prefix = $prefix;

        // Connect to db
        $this->dbh = $this->conn->connect();

        $this->injectAdapter(new DataAdapter());
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

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
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

    /**
     * Run query by QueryBuilder.
     *
     * @param array $definition QueryBuilder definition array
     * @param bool $autoexec
     *
     * @return \PDOStatement
     */
    public function qb(array $definition, $autoexec = false)
    {
        $builder = new QueryBuilder($definition);

        // Build sql string
        $this->sql = $builder->build();

        // Get params
        $this->params = $builder->getParams();

        return $this->query($autoexec);
    }

    /**
     * Run query by sql string.
     *
     * @param unknown $sql
     * @param unknown $params
     * @param string $autoexec
     * @return PDOStatement
     */
    public function sql($sql, array $params = [], $autoexec = false)
    {
        // Store Sql / definition and parameter
        $this->sql = (string) $sql;
        $this->params = $params;

        return $this->query($autoexec);
    }

    /**
     * This is the db query method.
     *
     * @param boolean $exec Optional autoexec flag
     *
     * @return \PDOStatement | queryresult
     */
    private function query($autoexec = false)
    {
        // Reset PDO statement
        $this->stmt = null;

        $this->stmt = $this->dbh->prepare(str_replace('{db_prefix}', $this->prefix, $this->sql));

        if (! empty($this->params)) {
            foreach ($this->params as $parameter => $value) {
                $data_type = $value === null ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $this->stmt->bindValue($parameter, $value, $data_type);
            }
        }

        if ($autoexec === true) {
            return $this->stmt->execute();
        }
        else {
            return $this->stmt;
        }
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
     * Returns all queried data.
     *
     * @param int $fetch_mode PDO fetch mode
     *
     * @return array
     */
    public function all($fetch_mode = \PDO::FETCH_ASSOC)
    {
        $this->stmt->execute();

        $data = $this->stmt->fetchAll($fetch_mode);

        if ($data) {
            $data = $this->adapter->setDataset($data)->getData();
        }

        return $data;
    }

    /**
     * PDO fetchAll
     *
     * @param PDO $fetch_mode PDO fetchmode constant
     *
     * @return mixed
     */
    public function fetchAll($fetch_mode = \PDO::FETCH_ASSOC)
    {
        $this->stmt->execute();

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
        $this->stmt->execute();

        $data = $this->stmt->fetch($fetch_mode);

        if ($data) {
            $data = $this->adapter->setData($data)->getData();
        }

        return $data;
    }

    /**
     * PDO fetch.
     *
     * @param PDO $fetch_mode PDO fetchmode constant
     *
     * @return mixed
     */
    public function fetch($fetch_mode = \PDO::FETCH_ASSOC)
    {
        $this->stmt->execute();

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
        $this->stmt->execute();

        return $this->stmt->fetchAll(\PDO::FETCH_COLUMN, $column);
    }

    /**
     * Returns value of first column in first row
     *
     * @return mixed
     */
    public function value()
    {
        $this->stmt->execute();

        return $this->stmt->fetchColumn();
    }

    /**
     * Shorthand method to perform Count(*) query
     *
     * @param string $table Table to delete from
     * @param string $filter Optional: Filterstatement (Defaul='')
     * @param array $params Optional: Parameter array to be used in filter
     *
     * @return number
     */
    public function count($table, $filter = '', array $params = [])
    {
        $query = [
            'table' => $table,
            'fields' => 'COUNT(*)'
        ];

        if ($filter) {

            $query['filter'] = $filter;

            if ($params) {
                $query['params'] = $params;
            }
        }

        $this->qb($query);

        return $this->value();
    }

    public function find($table, $key_field, $value, $fetch_mode = \PDO::FETCH_ASSOC)
    {
        $this->qb([
            'table' => $table,
            'filter' => $key_field . '=:' . $key_field,
            'params' => [
                ':' . $key_field => $value
            ]
        ]);

        return $this->single($fetch_mode);
    }

    /**
     * Shorthand delete method.
     *
     * @param string $table Table to delete from
     * @param string $filter Optional: Filterstatement (Defaul='')
     * @param array $params Optional: Parameter array to be used in filter
     */
    public function delete($table, $filter = '', array $params = [])
    {
        $query = [
            'table' => $table,
            'method' => 'DELETE'
        ];

        if ($filter) {
            $query['filter'] = $filter;

            if ($params) {
                $query['params'] = $params;
            }
        }

        $this->qb($query, true);

        return true;
    }

    /**
     * Returns number or rows in resultset.
     *
     * @return int
     */
    public function rowCount()
    {
        $this->stmt->execute();

        return $this->stmt->rowCount();
    }

    /**
     * Returns columncount of resultset
     *
     * @return number
     */
    public function columnCount()
    {
        $this->stmt->execute();

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
     * Commits PDO transaction.
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
     * Nullifies stmt and dbh properties to close connection.
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
    public function prepareArrayQuery($params='param', $values = [])
    {
        $params_names = [];
        $params_val = [];

        foreach ($values as $key => $val) {
            $name = ':' . $params . $key;
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
    public function debugSql($sql, $params = [])
    {
        if ($params) {

            $indexed = $params == array_values($params);

            foreach ($params as $k => $v) {

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

    /**
     * Creates and return an QueryBuilder instance.
     *
     * @return \Core\Lib\Data\Connectors\Db\QueryBuilder
     */
    public function getQueryBuilder()
    {
        return new QueryBuilder();
    }

    /**
     * Returns current sql string and parameter array.
     *
     * @return multitype:string array
     */
    public function getSqlAndParams()
    {
        return [
            'sql' => $this->sql,
            'params' => $this->params
        ];
    }
}
