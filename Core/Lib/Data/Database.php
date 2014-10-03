<?php
namespace Core\Lib\Data;

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

    public function __construct(\PDO $pdo, $prefix)
    {
        $this->dbh = $pdo;
        $this->prefix = $prefix;
    }

    public function setPrefix($db_prefix)
    {
        $this->prefix = $db_prefix;
        return $this;
    }

    /**
     * Converts as string => type to smf db type.
     * Returns false on failed conversion.
     * 
     * @param string $type
     * @return string|bool
     */
    public function convertType($type)
    {
        foreach ($this->conversionlist as $to_search => $new_type) {
            if (preg_match('/' . $to_search . '/', $type))
                return $new_type;
        }
        
        return false;
    }

    /**
     * Converts the table datafields into smf compatible datatypes for usage as parameter in queries
     * 
     * @param array $fieldlist
     * @return array $fieldlist
     */
    public function convFldTypes($fieldlist)
    {
        foreach ($fieldlist as $field => $type) {
            foreach ($this->conversionlist as $to_search => $new_type) {
                if (preg_match('/' . $to_search . '/', $type))
                    $fieldlist{$field} = $new_type;
            }
        }
        
        return $fieldlist;
    }

    private function checkPrefix()
    {
        if (strpos($this->tbl, '{db_prefix}') !== 0 || strpos($this->tbl, $this->prefix) !== 0)
            $this->tbl = $this->prefix . $this->tbl;
        else
            $this->tbl = str_replace('{db_prefix}', $this->prefix, $this->tbl);
    }

    /**
     * Get complex table structure.
     * 
     * @param string $tble The name of the table
     * @return An array of table structure - the name, the column info from {@link listTblColumns()} and the index info from {@link listTblIndexes()}
     */
    public function getTblStructure($tbl = '')
    {
        $this->tbl = $tbl;
        
        $this->checkPrefix();
        
        return new Data([
            'name' => $this->tbl,
            'columns' => $this->listTblColumns(),
            'indexes' => $this->listTblIndexes()
        ]);
    }

    /**
     * Return column information for a table.
     * 
     * @param string $tbl The name of the table to get column info for
     * @param bool $detail Whether or not to return detailed info. If true, returns the column info. If false, just returns the column names.
     * @return array An array of column names or detailed column info, depending on $detail
     */
    public function listTblColumns($tbl = '')
    {
        $this->query('SHOW FIELDS FROM `' . ($tbl ? $tbl : $this->tbl) . '`');
        $this->execute();
        $result = $this->resultset();
        
        $columns = [];
        
        foreach ($result as $row) {
            // Is column auto increment?
            $auto = strpos($row['Extra'], 'auto_increment') !== false ? true : false;
            
            // Size of field?
            if (preg_match('~(.+?)\s*\((\d+)\)(?:(?:\s*)?(unsigned))?~i', $row['Type'], $matches) === 1) {
                $type = $matches[1];
                $size = $matches[2];
                
                if (! empty($matches[3]) && $matches[3] == 'unsigned')
                    $unsigned = true;
            } else {
                $type = $row['Type'];
                $size = null;
            }
            
            $columns[$row['Field']] = new Data([
                'name' => $row['Field'],
                'null' => $row['Null'] != 'YES' ? false : true,
                'default' => isset($row['Default']) ? $row['Default'] : null,
                'type' => $type,
                'size' => $size,
                'auto' => $auto
            ]);
            
            if (isset($unsigned)) {
                $columns[$row['Field']]->unsigned = $unsigned;
                unset($unsigned);
            }
        }
        
        return $columns;
    }

    /**
     * Get index information.
     * 
     * @param string $tble The name of the table to get indexes for
     * @param bool $detail Whether or not to return detailed info.
     * @return array An array of index names or a detailed array of index info, depending on $detail
     */
    public function listTblIndexes($tbl = '')
    {
        $this->query('SHOW KEYS FROM `' . ($tbl ? $tbl : $this->tbl) . '`');
        $this->execute();
        $result = $this->resultset();
        
        $indexes = [];
        
        foreach ($result as $row) {
            // What is the type?
            if ($row['Key_name'] == 'PRIMARY')
                $type = 'primary';
            elseif (empty($row['Non_unique']))
                $type = 'unique';
            elseif (isset($row['Index_type']) && $row['Index_type'] == 'FULLTEXT')
                $type = 'fulltext';
            else
                $type = 'index';
                
                // This is the first column we've seen?
            if (empty($indexes[$row['Key_name']])) {
                $indexes[$row['Key_name']] = [
                    'name' => $row['Key_name'],
                    'type' => $type,
                    'columns' => []
                ];
            }
            
            // Is it a partial index?
            if (! empty($row['Sub_part']))
                $indexes[$row['Key_name']]['columns'][] = $row['Column_name'] . '(' . $row['Sub_part'] . ')';
            else
                $indexes[$row['Key_name']]['columns'][] = $row['Column_name'];
        }
        
        return new Data($indexes);
    }

    /**
     * This is the db query method.
     * 
     * @param string $sql
     * @param optional array $params
     * @return \PDOStatement
     */
    public function query($sql)
    {
        $this->stmt = $this->dbh->prepare(str_replace('{db_prefix}', $this->prefix, $sql));
        return $this->stmt;
    }

    /**
     * Bind Parameter
     * 
     * @param unknown $param
     * @param unknown $value
     * @param string $type
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
        
        if (is_array($value))
            $value = implode(', ', $value);
        
        $this->stmt->bindValue($param, $value, $type);
        return $this->stmt;
    }

    /**
     * Bind Parameter
     * 
     * @param unknown $param
     * @param unknown $value
     * @param string $type
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
        
        if (is_array($value))
            $value = implode(', ', $$value);
        
        $this->stmt->bindParam($param, $value, $type);
        return $this->stmt;
    }

    public function execute()
    {
        return $this->stmt->execute();
    }

    public function resultset($fetch_mode = \PDO::FETCH_ASSOC)
    {
        return $this->stmt->fetchAll($fetch_mode);
    }

    public function single($fetch_mode = \PDO::FETCH_ASSOC)
    {
        return $this->stmt->fetch($fetch_mode);
    }

    public function column($column = 0)
    {
        return $this->stmt->fetchAll(\PDO::FETCH_COLUMN, $column);
    }

    public function value()
    {
        return $this->stmt->fetchColumn();
    }

    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    public function columnCount()
    {
        return $this->stmt->columnCount();
    }

    public function lastInsertId()
    {
        return $this->dbh->lastInsertId();
    }

    public function beginTransaction()
    {
        return $this->dbh->beginTransaction();
    }

    public function endTransaction()
    {
        return $this->dbh->commit();
    }

    public function cancelTransaction()
    {
        return $this->dbh->rollBack();
    }

    public function debugDumpParams()
    {
        return $this->stmt->debugDumpParams();
    }

    public function close()
    {
        return true;
    }

    /**
     * This is the db query method.
     * It'S a simple wrapper for the $smcFunc['db_quote']
     * 
     * @param string $sql
     * @param optional array $params
     * @return string $res
     * @todo Not working for now
     */
    public function quote($sql, $params = [])
    {}

    /**
     *
     * @param string $method "Insert" or "Replace"
     * @param string $tbl Full name of the table to insert data. Do not forget {db_prefix}!
     * @param array $fields Array of the coloums we have data for
     * @param array $values The value to insert
     * @param array $keys Array of table keys
     * @return integer The id of the last inserted record
     */
    public function insert($method, $tbl, $fields, $values, $keys)
    {
        // Generate field and parameter list
        $field_list = array_keys($fields);
        $param_list = [];
        
        foreach ($field_list as $field_name)
            $param_list[] = ':' . $field_name;
        
        $stmt = $this->dbh->prepare('INSERT INTO ' . $tbl . ' (' . implode(', ', $field_list) . ') VALUES (' . implode(', ', $param_list) . ')');
        
        foreach ($fields as $param => &$var)
            $stmt->bindParam(':' . $param, $var);
        
        $stmt->execute();
        
        return $this->dbh->lastInsertId($keys[0]);
    }

    /**
     * Queries DB an returns the first requested colums as array
     * Perfect for queries wher you only want to get keys
     *
     * @param string $sql
     * @return array $arr
     */
    public function getKeys($sql, $params = [], $column_number = 0, $param_mode = 0)
    {
        $this->query($sql);
        
        foreach ($params as $param => &$val) {
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
            
            foreach ($serialized as $col_to_unserialize)
                $record{$col_to_unserialize} = $this->checkSerialized($record{$col_to_unserialize});
                
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
        
        foreach ($serialized as $col_to_unserialize)
            $row{$col_to_unserialize} = $this->checkSerialized($row{$col_to_unserialize});
        
        return $row;
    }

    /**
     * Queries the DB and returns result row in form of an array with an automated numeric index.
     *
     * This method is ideal for databaserequests where you only want
     * to retreive on row.
     *
     * @param string $sql
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
     * @return array - data of the requested column
     */
    public function getTwoCols($sql, $params = [], $serialized = [])
    {
        $stmt = $this->query($sql, $params);
        
        $data = [];
        
        while ($row = $stmt->fetch(\PDO::FETCH_NUM))
            $data[$row[0]] = $row[1];
        
        return $data;
    }

    /**
     * Queries the db and returns only one value.
     * Ideal for SELECT Count(*) or similiar requests.
     * 
     * @param string $sql - ignores all requested columns except the first and second
     * @param array $params - possible filter parameter
     * @return mixed $val - requested value
     */
    public function getOneValue($sql, $params = [], $column = 0)
    {
        $value = $this->query($sql, $params)->fetchColumn($column);
        
        if ($value)
            $value = $this->checkSerialized($value);
        
        return $value;
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

    private function checkSerialized($val = null)
    {
        if ($val === null)
            return $val;
        
        return $this->isSerialized($val) ? unserialize($val) : $val;
    }
}
