<?php
namespace Core\AppsSec\Core\Model;

use Core\Lib\Amvc\Model;
use Core\Lib\Data\Container;

/**
 * Security Model
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license MIT
 */
class SecurityModel extends Model
{

    public function getEmptyLogin()
    {
        $data = $this->getContainer('Core', 'Security');

        $data['remember'] = 1;

        return $data;
    }

    public function doLogin(Container $data)
    {
        // Set validation rules and validate data
        $data->validate();

        // End on validation errors and return data container
        if ($data->hasErrors()) {
            return $data;
        }

        /* @var $security \Core\Lib\Security\Security */
        $security = $this->di->get('core.sec.security');
        $security->login($data['login'], $data['password'], isset($data['remember']));

        if ($security->loggedIn() === true) {
            $data['logged_in'] = true;
        }
        else {
            $data['logged_in'] = false;
            $data->addError('@', $this->txt('login_failed'));
        }

        return $data;
    }

    public function doLogout()
    {
        $this->di->get('core.sec.security')->logout();
    }
}
