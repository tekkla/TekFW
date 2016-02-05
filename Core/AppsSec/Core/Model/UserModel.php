<?php
namespace Core\AppsSec\Core\Model;

use Core\Lib\Amvc\Model;

/**
 * UserModel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class UserModel extends Model
{

    public function getList($field = 'display_name', $needle = '%', $limit = 100, array $callbacks = [])
    {
        $qb = [
            'table' => $this->table,
            'filter' => $field . ' LIKE :' . $field,
            'params' => [
                ':' . $field => $needle
            ]
        ];
        
        if ($limit) {
            $qb['limit'] = 100;
        }
        
        $db = $this->getDbConnector();
        
        if ($callbacks) {
            $db->addCallbacks($callbacks);
        }
        
        $db->qb($qb);
        
        return $db->all();
    }
}