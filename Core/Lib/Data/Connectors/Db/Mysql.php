<?php
namespace Core\Lib\Data\Connectors\Db;

/**
 * Mysql.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Mysql extends TableInfoAbstract
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Db\TableInfoInterface::listTblColumns()
     */
    public function loadColumns($tbl)
    {
        $this->db->sql('SHOW FIELDS FROM `' . $tbl . '`');
        $result = $this->db->all();

        $columns = [];

        foreach ($result as $row) {

            $name = $row['Field'];
            $null = $row['Null'] != 'YES' ? false : true;
            $default = isset($row['Default']) ? $row['Default'] : null;
            $unsigned = false;

            // Is column auto increment?
            $auto = strpos($row['Extra'], 'auto_increment') !== false ? true : false;

            // Size of field?
            if (preg_match('~(.+?)\s*\((\d+)\)(?:(?:\s*)?(unsigned))?~i', $row['Type'], $matches) === 1) {

                $type = $matches[1];
                $size = $matches[2];

                if (! empty($matches[3]) && $matches[3] == 'unsigned') {
                    $unsigned = true;
                }
            }
            else {
                $type = $row['Type'];
                $size = null;
            }

            $this->addColumn($name, $null, $default, $type, $size, $auto, $unsigned);
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Data\Db\TableInfoInterface::listTblIndexes()
     */
    public function loadIndexes($tbl)
    {
        $this->db->sql('SHOW KEYS FROM `' . $tbl . '`');
        $result = $this->db->all();

        $indexes = [];

        foreach ($result as $row) {

            $name = $row['Key_name'];

            // What is the type?
            if ($row['Key_name'] == 'PRIMARY') {
                $type = 'primary';
            }
            elseif (empty($row['Non_unique'])) {
                $type = 'unique';
            }
            elseif (isset($row['Index_type']) && $row['Index_type'] == 'FULLTEXT') {
                $type = 'fulltext';
            }
            else {
                $type = 'index';
            }

            // Is it a partial index?
            $column = ! empty($row['Sub_part']) ? $row['Column_name'] . '(' . $row['Sub_part'] . ')' : $row['Column_name'];

            $this->addIndex($name, $type, $column);
        }
    }
}
