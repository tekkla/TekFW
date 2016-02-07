<?php
namespace Core\AppsSec\Core\Model;

use Core\Lib\Amvc\Model;
use Core\Lib\Data\Container\Container;

/**
 * UserModel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class RegisterModel extends Model
{

    private $table = 'users';

    public function getAll(array $callbacks = [])
    {
        $db = $this->getDbConnector();
        
        if ($callbacks) {
            $db->addCallbacks($callbacks);
        }
        
        $db->qb([
            'table' => $this->table
        ]);
        
        return $db->all();
    }

    public function getUser($id_user)
    {
        $db = $this->getDbConnector();
        $db->qb([
            'table' => $this->table,
            'filter' => 'id_user = :id_user',
            'params' => [
                ':id_user' => $id_user
            ]
        ]);
        
        return $db->single();
    }

    public function getRegister()
    {
        return $this->getContainer();
    }

    public function register(Container $data, $activate)
    {
        $data->filter();
        
        $data->setValidation('password_compare', $data->getValidation('password'));
        
        if (! password_verify($data['password'], password_hash($data['password_compare'], PASSWORD_DEFAULT))) {
            $data->addError('password', $this->txt('passwords_mismatch'));
            $data->addError('password_compare', $this->txt('passwords_mismatch'));
        }
        
        $data->validate();
        
        if ($data->hasErrors()) {
            return;
        }
        
        $db = $this->getDbConnector();
        
        // Check for already existing username
        $exists = $db->count($this->table, 'username=:username', [
            ':username' => $data['username']
        ]);
        
        if ($exists) {
            $data->addError('username', $this->txt('user_username_already_in_use'));
            return;
        }
        
        return $this->di->get('core.security.users')->createUser($data['username'], $data['password'], $activate);
    }

    public function getActivationData($id_user)
    {
        $db = $this->getDbConnector();
        $db->qb([
            'table' => 'activation_tokens',
            'fields' => [
                'selector',
                'token',
                'expires'
            ],
            'filter' => 'id_user=:id_user',
            'params' => [
                ':id_user' => $id_user
            ]
        ]);
        
        return $db->single();
    }

    public function getEmptyLogin()
    {
        $data = $this->getContainer('Core', 'Security');
        
        // Autologin on or off by default?
        $data['remember'] = $this->cfg('security.autologin');
        
        return $data;
    }

    public function doLogin(Container $data)
    {
        // End on validation errors and return data container
        if (! $data->validate()) {
            return false;
        }
        
        /* @var $security \Core\Lib\Security\Security */
        $security = $this->di->get('core.security');
        $security->login($data['login'], $data['password'], isset($data['remember']) ? (bool) $data['remember'] : false);
        
        if ($security->loggedIn() === true) {
            return true;
        }
        else {
            return false;
        }
    }
}