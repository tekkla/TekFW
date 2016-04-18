<?php
namespace AppsSec\Core\Model;

use Core\Amvc\Model;

class LogModel extends Model
{

    public function getLogs($entries = 20)
    {
        $qb = [
            'table' => 'core_logs',
            'order' => 'logdate DESC',
            'limit' => $entries
        ];
        
        $db = $this->getDbConnector();
        $db->qb($qb);
        
        return $db->all();
    }
}

