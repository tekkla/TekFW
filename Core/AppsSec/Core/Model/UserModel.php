<?php
namespace Core\AppsSec\Core\Model;

use Core\Lib\Amvc\Model;

/**
 * UserModel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class UserModel extends Model
{

    private $table = 'users';

    public function getAll()
    {
        $adapter = $this->getDbAdapter();
        $adapter->qb([
            'table' => $this->table,
        ]);

        return $adapter->all();
    }

    public function getEdit($id_user)
    {
        $adapter = $this->getDbAdapter();
        $adapter->qb([
            'table' => $this->table,
            'filter' => 'id_user = :id_user',
            'params' => [
                'id_user' => $id_user
            ]
        ]);

        return $adapter->single();
    }

}

