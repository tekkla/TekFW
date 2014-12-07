<?php
namespace Core\AppsSec\Core\Model;

use Core\Lib\Amvc\Model;

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
        $data = $this->getContainer();

        $data->createField('login', 'string');
        $data->createField('password', 'string');
        $data->createField('remember', 'int');

        $data['remember'] = 1;

        return $data;
    }

    public function doLogin($data)
    {
        // Create empty data container
        $container = $this->getContainer();

        // Use fill method to create fields
        $container->fill($data);

        // Set validation rules and validate data
        $container->setValidation('login', [
            'required',
            'empty'
        ]);

        $container->setValidation('password', [
            'required',
            'empty'
        ]);

        $container->validate();

        // End on validation errors and return data container
        if ($container->hasErrors()) {
            return $container;
        }

        // Acces security lib and do login
        $security = $this->di->get('core.sec.security');
        $security->login($container['login'], $container['password'], isset($container['remember']));

        if ($security->loggedIn() === true) {
            $container['logged_in'] = true;
        }

        return $container;
    }

    public function doLogout()
    {
        $this->di->get('core.sec.security')->logout();
    }
}
