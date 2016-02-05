<?php
namespace Core\AppsSec\Core\Model;

use Core\Lib\Amvc\Model;
use Core\Lib\Data\Container\Container;

/**
 * SecurityModel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class LoginModel extends Model
{

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
        $security = $this->di->get('core.sec.security');
        $security->login($data['login'], $data['password'], isset($data['remember']) ? (bool) $data['remember'] : false);
        
        if ($security->loggedIn() === true) {
            return true;
        } else {
            return false;
        }
    }
}
