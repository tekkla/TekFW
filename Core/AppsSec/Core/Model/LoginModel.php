<?php
namespace Core\AppsSec\Core\Model;

use Core\Lib\Amvc\Model;

class LoginModel extends Model
{

    protected $scheme = [
        'fields' => [
            'username' => [
                'type' => 'string',
                'validate' => [
                    'empty'
                ],
                'filter' => [
                    FILTER_SANITIZE_STRING
                ]
            ],
            'password' => [
                'type' => 'string',
                'validate' => [
                    'empty'
                ]
            ],
            'remember' => [
                'type' => 'int'
            ]
        ]
    ];

    public function checkLoginData(&$data)
    {
        $this->validate($data);
    }
}

?>