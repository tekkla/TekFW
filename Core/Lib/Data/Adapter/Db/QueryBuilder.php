<?php
namespace Core\Lib\Data\Adapter\Db;

use Core\Lib\Data\Container;
use Core\Lib\Traits\ArrayTrait;
use Core\Lib\Traits\ConvertTrait;

/**
 * QueryBuilder
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class QueryBuilder
{

    use ArrayTrait;
    use ConvertTrait;

    private $method;

    private $definition;

    private $tbl;

    private $alias = '';

    private $fields = [];

    private $values = [];

    private $join = [];

    private $filter = '';

    private $params = [];

    private $order = '';

    private $group_by = '';

    private $having = '';

    private $sql = '';

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
    public function __construct(Array $definition = [])
    {
        if ($definition) {
            $this->processQueryDefinition($definition);
        }
    }

    /**
     * SELECT statement
     *
     * @param array|string $fields Fieldlist as comma seperated string or value array.
     *
     * @return \Core\Lib\Data\Adapter\Db\QueryBuilder
     */
    public function Select($fields = '')
    {
        $this->method = 'SELECT';

        $this->Columns($fields);

        return $this;
    }

    /**
     * SELECT DISTINCT statement
     *
     * @param array|string $fields Fieldlist as comma seperated string or value array.
     *
     * @return \Core\Lib\Data\Adapter\Db\QueryBuilder
     */
    public function SelectDistinct($fields = '')
    {
        $this->method = 'SELECT DISTINCT';

        $this->Columns($fields);

        return $this;
    }

    /**
     * INSERT INTO statement
     *
     * @return \Core\Lib\Data\Adapter\Db\QueryBuilder
     */
    public function InsertInto()
    {
        $this->method = 'INSERT INTO';

        return $this;
    }

    /**
     * INTO statement
     *
     * @param string $tbl Name of table
     *
     * @return \Core\Lib\Data\Adapter\Db\QueryBuilder
     */
    public function Into($tbl)
    {
        $this->From($tbl);

        return $this;
    }

    /**
     * DELETE statement
     *
     * @return \Core\Lib\Data\Adapter\Db\QueryBuilder
     */
    public function Delete()
    {
        $this->method = 'DELETE';

        return $this;
    }

    /**
     * Colums to use in query
     *
     * @param array|string $fields Fieldlist as comma seperated string or value array.
     *
     * @return \Core\Lib\Data\Adapter\Db\QueryBuilder
     */
    public function Columns($columns = '')
    {
        if (empty($columns)) {
            return;
        }

        if (! is_array($columns)) {
            $this->fields = explode(',', $columns);
        }

        $this->fields = array_map('trim', $this->fields);

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

            if (! is_array($this->fields)) {
                $this->fields = (array) $this->fields;
            }

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
                // if (strpos($field, '.') === false) {
                // $field .= ($this->alias ? $this->alias : $this->tbl) . '.' . $field;
                // }

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

        switch ($this->method) {

            case 'UPDATE':

                // we have to build our own fieldlist
                $fields = [];

                foreach ($this->fields as $field) {
                    $fields[] = $field . '=:' . $field;
                }

                $fieldlist = implode(',', $fields);

                $this->sql = $this->method . ' {db_prefix}' . $tbl . ' SET ' . $fieldlist . $filter;

                break;

            case 'INSERT':
            case 'REPLACE':
                $fieldlist = ! $fieldlist || $fieldlist == '*' ? '' : ' (' . $fieldlist . ')';

                // Build values
                $values = [];

                foreach ($this->fields as $field) {
                    $values[] = ':' . $field;
                }

                $values = implode(', ', $values);

                $this->sql = $this->method . ' INTO {db_prefix}' . $tbl . $fieldlist . ' VALUES (' . $values . ')';
                break;

            case 'DELETE':
                $this->sql = $this->method . ' FROM {db_prefix}' . $tbl . $filter . $order . $limit;
                break;

            default:
                $this->sql = $this->method . ' ' . $fieldlist . ' FROM {db_prefix}' . $tbl . $join . $filter . $group_by . $having . $order . $limit;
                break;
        }

        // Finally cleanup parameters by removing parameter not needed in query and parse array parameter into sql string.
        foreach ($this->params as $key => $val) {

            // Do cleanup
            if (strpos($this->sql, $key) === false) {
                unset($this->params[$key]);
                continue;
            }

            // Replace array parameter against sql valid part
            if (is_array($val)) {
                $prepared = $this->prepareArrayQuery($key, $val);
            }
        }

        return $this->sql;
    }

    private function processQueryDefinition($def)
    {
        // Store defintion
        $this->definition = $def;

        // Use set query type or use 'row' as default
        if (isset($this->definition['method'])) {
            $this->method = strtoupper($this->definition['method']);
            unset($this->definition['method']);
        }
        else {
            $this->method = 'SELECT';
        }

        // All methods need the table defintion
        $this->processTblDefinition();

        // Process data definition?
        if (isset($this->definition['data'])) {
            $this->processDataDefinition();
        }

        switch ($this->method) {

            case 'INSERT':
            case 'REPLACE':
                $this->processInsert();
                break;

            case 'UPDATE':
                $this->processUpdate();
                break;

            case 'DELETE':
                $this->processDelete();
                break;

            case 'SELECT':
            default:
                $this->processSelect();
                break;
        }

        return $this;
    }

    private function processSelect()
    {
        $this->processTblDefinition();

        $this->processFieldDefinition();

        $this->processFilterDefinition();

        $this->processParamsDefinition();

        $this->processGroupByDefinition();

        $this->processHavingDefinition();

        $this->processJoinDefinition();

        $this->processOrderDefinition();

        $this->processLimitDefinition();
    }

    /**
     * Processes insert definition
     *
     * @throws \RuntimeException
     */
    private function processInsert()
    {
        if (! isset($this->definition['fields']) && ! isset($this->definition['field'])) {
            Throw new \RuntimeException('QueryBuilder need a "field" or "fields" list element to process "INSERT" definition.');
        }

        if (! isset($this->definition['params'])) {
            Throw new \RuntimeException('QueryBuilder need a assoc array param list to process "INSERT" definition.');
        }

        $this->processFieldDefinition();
        $this->processParamsDefinition();
    }

    private function processUpdate()
    {
        if (! isset($this->definition['fields']) && ! isset($this->definition['field'])) {
            Throw new \RuntimeException('QueryBuilder need a "field" or "fields" list element to process "INSERT" definition.');
        }
        if (! isset($this->definition['params'])) {
            Throw new \RuntimeException('QueryBuilder need a assoc array param list to process "INSERT" definition.');
        }

        $this->processFieldDefinition();
        $this->processFilterDefinition();
        $this->processParamsDefinition();

        $this->processLimitDefinition();
        $this->processOrderDefinition();
    }

    private function processDelete()
    {
        $this->processFieldDefinition();
        $this->processFilterDefinition();
        $this->processOrderDefinition();
        $this->processLimitDefinition();
        $this->processParamsDefinition();
    }

    /**
     * Processes value defintion.
     * Takes also care of set alias definition.
     */
    private function processTblDefinition()
    {
        if (! isset($this->definition['tbl']) && ! isset($this->definition['table'])) {
            Throw new \RuntimeException('QueryBuilder needs a table name. Provide tablename by setting "tbl" or "table" element in your query definition');
        }
        else {
            $this->tbl = isset($this->definition['tbl']) ? $this->definition['tbl'] : $this->definition['table'];
        }

        if (isset($this->definition['alias'])) {
            $this->alias = $this->definition['alias'];
        }
    }

    /**
     * Processes field definition.
     * Uses '*' as field when no definition is set.
     */
    private function processFieldDefinition()
    {
        if (isset($this->definition['field'])) {
            $this->fields = $this->definition['field'];
        }
        elseif (isset($this->definition['fields'])) {
            $this->fields = $this->definition['fields'];
        }
        else {
            $this->fields = '*';
        }
    }

    /**
     * Processes data defintion
     *
     * Take care of data type. Uses container field names for field list
     * and field values for paramters.
     */
    private function processDataDefinition()
    {
        // Data definition only as \Core\Lib\Data\Container ever<thing else causes an exception
        if (! $this->definition['data'] instanceof Container) {
            Throw new \RuntimeException('QueryBuilder can only process data attributes of type \Core\Lib\Data\Container.');
        }

        // Autodetection of method when none is set e.g. SELECT is set.
        if ($this->method == 'SELECT') {

            // Get name of primary field. Is false when no primary field exists.
            $primary = $this->definition['data']->getPrimary();

            // Update mathod to UPDATE when primary exists and has a value. Otherwise we insert a new record.
            $this->method = ($primary !== false && !empty($this->definition['data'][$primary])) ? 'UPDATE' : 'INSERT';
        }

        // Check for allowed querymethods
        $allowed_methods = [
            'UPDATE',
            'INSERT',
            'REPLACE'
        ];

        if (! in_array($this->method, $allowed_methods)) {
            Throw new \RuntimeException('QueryBuilder can only process querymethods of type "update", "insert" or "replace".');
        }

        /* @var $field \Core\Lib\Data\Field */
        foreach ($this->definition['data'] as $field_name => $field) {

            // Value handling
            $value = $field->get();

            // Field is flagged as serialized?
            if ($field->getSerialize()) {
                $value = serialize($value);
            }

            // Handle fields flagged as primary
            if ($field->getPrimary()) {

                // Different modes need different handlings
                switch ($this->method) {

                    // Ignore field when mode is insert
                    case 'INSERT':
                        continue;
                        break;

                    // Use field as filter on update
                    case 'UPDATE':
                        $this->definition['filter'] = $field_name . ' = :__primary_' . $field_name;
                        $this->definition['params'][':__primary_' . $field_name] = $value;
                        break;

                    // Add field to fieldlist on replace
                    case 'REPLACE':
                    default:
                        $this->definition['fields'][] = $field_name;
                        $this->definition['params'][':' . $field_name] = $value;
                        break;
                }
            }
            else {

                // Simple field and value
                $this->definition['fields'][] = $field_name;
                $this->definition['params'][':' . $field_name] = $value;
            }
        }
    }

    /**
     * Processes join defintion.
     * Such definition can be an assoc or indexed array.
     */
    private function processJoinDefinition()
    {
        if (isset($this->definition['join']) && is_array($this->definition['join'])) {

            foreach ($this->definition['join'] as $join) {

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
    }

    /**
     * Processes group defintion
     */
    private function processGroupByDefinition()
    {
        if (isset($this->definition['group'])) {
            $this->group_by = $this->definition['group'];
        }
    }

    /**
     * Processes having defintion
     */
    private function processHavingDefinition()
    {
        if (isset($this->definition['having'])) {
            $this->having = $this->definition['having'];
        }
    }

    /**
     * Processes filter defintion.
     * When Filter is an array
     */
    private function processFilterDefinition()
    {
        if (isset($this->definition['filter'])) {

            if (is_array($this->definition['filter'])) {

                if (! count($this->definition['filter']) == 2) {
                    Throw new \RuntimeException('Querybuilder needs two elements when filter is set as array. The first element has to be the filter statement. The second one an assoc array with filter parameters');
                }

                if (! $this->isAssoc($this->definition['filter'][1])) {
                    Throw new \RuntimeException('Querybuilder needs an assoc array as filter parameter list.');
                }

                $this->filter = $this->definition['filter'][0];

                // Set params
                $this->setParameter($this->definition['filter'][1]);
            }
            else {

                $this->filter = $this->definition['filter'];
            }
        }
    }

    /**
     * Processes limit defintion
     */
    private function processLimitDefinition()
    {
        if (isset($this->definition['limit'])) {
            // Single int value as limit?
            if (is_int($this->definition['limit'])) {
                $this->limit['lower'] = $this->definition['limit'];
            }

            // Array but only one value`?
            elseif (is_array($this->definition['limit']) && count($this->definition['limit']) == 1) {
                $this->limit['lower'] = (int) $this->definition['limit'][0];
            }

            // Array and two values?
            elseif (is_array($this->definition['limit']) && count($this->definition['limit']) == 2) {
                $this->limit['lower'] = (int) $this->definition['limit'][0];
                $this->limit['upper'] = (int) $this->definition['limit'][1];
            }
        }
    }

    /**
     * Processes order defintion
     */
    private function processOrderDefinition()
    {
        if (isset($this->definition['order'])) {
            $this->order = $this->definition['order'];
        }
    }

    /**
     * Processes parameter definition
     */
    private function processParamsDefinition()
    {
        $extend = isset($this->definition['params']) ? 's' : '';

        if (isset($this->definition['param' . $extend])) {
            $this->setParameter($this->definition['param' . $extend]);
        }
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
    public function setParameter($arg1, $arg2 = null)
    {
        if ($arg2 === null) {

            if (! is_array($arg1) || ! $this->isAssoc($arg1)) {
                Throw new \InvalidArgumentException('Setting one QueryBuilder argument means you have to set an assoc array as argument.');
            }

            $this->params = array_merge($this->params, $arg1);
        }
        else {
            $this->params[$arg1] = $arg2;
        }

        return $this;
    }

    /**
     * Returns all parameters.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Creates a string of named parameters and an array of named parameters => values.
     *
     * @param string $param
     * @param array $values
     *
     * @return array
     */
    private function prepareArrayQuery($param_name, array $params)
    {
        $params_names = [];
        $params_val = [];

        foreach ($params as $key => $val) {
            $name = ':' . 'arr_' . $key;
            $params_name[] = $name;
            $params_val[$name] = $val;
        }

        $sql = implode(', ', $params_name);

        str_replace($param_name, $sql, $this->sql);
        array_merge($this->params, $params_val);

        return [
            'sql' => $sql,
            'values' => $params_val
        ];
    }
}
