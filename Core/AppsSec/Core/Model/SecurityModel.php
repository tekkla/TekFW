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
        $data = $this->getGenericContainer();

        $data->createField('login', 'string');
        $data->createField('password', 'string');
        $data->createField('remember', 'int');

        $data['remember'] = 1;

        return $data;
    }

    public function doLogin(Container $data)
    {
        // Set validation rules and validate data
        $data->setValidation('login', [
            'required',
            'empty'
        ]);

        $data->setValidation('password', [
            'required',
            'empty'
        ]);

        $data->validate();

        // End on validation errors and return data container
        if ($data->hasErrors()) {
            return $data;
        }

        // Acces security lib and do login
        $security = $this->di->get('core.sec.security');
        $security->login($data['login'], $data['password'], isset($data['remember']));

        if ($security->loggedIn() === true) {
            $data['logged_in'] = true;
        }
        else {
            $data->addError('@', 'Login failed.');
        }

        return $data;
    }

    public function doLogout()
    {
        $this->di->get('core.sec.security')->logout();
    }
}
