<?php
namespace Core\AppsSec\Core;

use Core\Lib\Amvc\App;

/**
 * Core.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
final class Core extends App
{
    // Flag as secured app
    protected $secure = true;

    // Uses language system
    protected $language = true;

    protected $permissions = [
        'admin.groups',
        'admin.user',
        'admin.log',
    ];

    // Apps default config
    protected $config = [

        'site' => [
            'general' => [
                [
                    'name' => 'name',
                ],
                [
                    'name' => 'url',
                    'control' => 'url',
                    'validate' => [
                        'url'
                    ]
                ],
                [
                    'name' => 'webmaster_email',
                    'control' => 'mail'
                ]
            ],
            'language' => [
                [
                    'name' => 'default',
                    'control' => 'select',
                    'data' => [
                        'array',
                        [
                            'en',
                            'de'
                        ],
                        1
                    ],
                    'default' => 'en'
                ]
            ]
        ],
        'security' => [
            'user' => [
                'username' => [
                    [
                        'name' => 'min_length',
                        'type' => 'int',
                        'control' => 'number',
                        'default' => 8,
                        'validate' => [
                            'empty'
                        ]
                    ],
                    [
                        'name' => 'regexp'
                    ]
                ],
                'password' => [
                    [
                        'name' => 'min_length',
                        'control' => 'number',
                        'default' => 8
                    ],
                    [
                        'name' => 'max_length',
                        'control' => 'number',
                        'default' => 4096,
                        'validate' => [
                            'empty',
                            [
                                'max',
                                [
                                    8,
                                    4096
                                ]
                            ]
                        ]
                    ],
                    [
                        'name' => 'regexp'
                    ]
                ]
            ],
            'register' => [
                [
                    'name' => 'use_compare_password',
                    'control' => 'switch',
                    'default' => 1
                ]
            ],
            'activation' => [
                [
                    'name' => 'use',
                    'control' => 'switch',
                    'default' => 1
                ],
                [
                    'name' => 'ttl',
                    'control' => 'number',
                    'default' => 3600
                ],
                [
                    'name' => 'mta',
                    'default' => 'default'
                ],
                [
                    'name' => 'from'
                ],
                [
                    'name' => 'name'
                ]
            ],
            'login' => [
                [
                    'name' => 'autologin',
                    'control' => 'switch',
                    'default' => 1
                ]
            ],
            'ban' => [
                [
                    'name' => 'tries',
                    'control' => 'number',
                    'default' => 5
                ],
                'ttl' => [
                    [
                        'name' => 'log',
                        'control' => 'number',
                        'default' => 300
                    ],
                    [
                        'name' => 'ban',
                        'control' => 'number',
                        'default' => 600
                    ]
                ]
            ]
        ],

        // Group: Execute
        'execute' => [

            'default' => [

                [
                    'name' => 'app',
                    'default' => 'Core'
                ],
                [
                    'name' => 'controller',
                    'default' => 'Index'
                ],
                [
                    'name' => 'action',
                    'default' => 'Index'
                ]
            ],
            'content' => [

                [
                    'name' => 'handler'
                ]
            ]
        ],
        // Group: JS
        'js' => [
            'general' => [
                [
                    'name' => 'position',
                    'control' => 'select',
                    'data' => [
                        'array',
                        [
                            't' => 'Top',
                            'b' => 'Bottom'
                        ],
                        0
                    ],
                    'default' => 't'
                ]
            ],

            'jquery' => [
                [
                    'name' => 'version',
                    'default' => '2.2.0'
                ],
                [
                    'name' => 'local',
                    'control' => 'switch',
                    'default' => 1
                ]
            ],
            'style' => [

                [
                    'name' => 'fadeout_time',
                    'control' => [
                        'number',
                        [
                            'min' => 100
                        ]
                    ],
                    'default' => 5000,
                    'validate' => [
                        'empty',
                        [
                            'min',
                            100
                        ]
                    ]
                ]
            ]
        ],
        'style' => [
            // Bootstrap
            'bootstrap' => [
                [
                    'name' => 'version',
                    'control' => 'input',
                    'default' => '3.3.6',
                    'validate' => [
                        'empty'
                    ]
                ],
                [
                    'name' => 'local',
                    'control' => 'switch',
                    'default' => 1
                ]
            ],
            'fontawesome' => [

                [
                    'name' => 'version',
                    'default' => '4.5.0',
                    'validate' => [
                        'empty'
                    ]
                ],
                [
                    'name' => 'local',
                    'control' => 'switch',
                    'default' => 1
                ]
            ],
            'theme' => [

                [
                    'name' => 'name',
                    'control' => 'text',
                    'default' => 'Core',
                    'validate' => [
                        'empty'
                    ]
                ]
            ]
        ],
        // Error handling
        'error' => [
            'display' => [
                [
                    'name' => 'skip_security_check',
                    'control' => 'switch'
                ]
            ],
            'mail' => [
                [
                    'name' => 'use',
                    'control' => 'switch',
                    'default' => 1
                ],
                [
                    'name' => 'address'
                ],
                [
                    'name' => 'mta',
                    'default' => 'default'
                ]
            ],
            'log' => [

                [
                    'name' => 'use',
                    'control' => 'switch',
                    'default' => 1
                ],
                'modes' => [
                    [
                        'name' => 'db',
                        'control' => 'switch',
                        'default' => 1
                    ],
                    [
                        'name' => 'php',
                        'control' => 'switch',
                        'default' => 1
                    ]
                ]
            ]
        ],

        // Caching
        'cache' => [
            'file' => [
                [
                    'name' => 'ttl',
                    'control' => 'number',
                    'default' => '3600'
                ],
                [
                    'name' => 'ttl_js',
                    'control' => 'number',
                    'default' => '3600'
                ],
                [
                    'name' => 'ttl_css',
                    'control' => 'number',
                    'default' => '3600'
                ]
            ],
            'memcache' => [
                [
                    'name' => 'use',
                    'control' => 'switch'
                ],
                [
                    'name' => 'server'
                ],
                [
                    'name' => 'port'
                ]
            ]
        ],

        // Loggingsystem
        'log' => [
            'display' => [
                [
                    'name' => 'entries',
                    'control' => 'number',
                    'default' => 20
                ]
            ]
        ],

        // Mailsystem
        'mail' => [
            'general' => [
                [
                    'name' => 'smtpdebug',
                    'control' => 'switch'
                ]
            ],
            'mta' => [
                'default' => [
                    [
                        'name' => 'system',
                        'control' => 'select',
                        'data' => [
                            'array',
                            [
                                0 => 'phpmail',
                                1 => 'SMTP'
                            ],
                            0
                        ],
                        'default' => 1
                    ],
                    [
                        'name' => 'host',
                        'control' => 'mail'
                    ],
                    [
                        'name' => 'port',
                        'control' => 'number',
                        'default' => 587
                    ],
                    [
                        'name' => 'username'
                    ],
                    [
                        'name' => 'password',
                        'control' => 'password'
                    ],
                    [
                        'name' => 'accept_selfsigned',
                        'control' => 'switch'
                    ],
                    [
                        'name' => 'protocol',
                        'control' => 'select',
                        'data' => [
                            'array',
                            [
                                'ssl',
                                'tls'
                            ],
                            1
                        ],
                        'default' => 'tls'
                    ]
                ]
            ]
        ]
    ];

    // Apps routes
    protected $routes = [
        [
            'name' => 'index',
            'route' => '../',
            'controller' => 'Index',
            'action' => 'Index'
        ],
        [
            'name' => 'login',
            'method' => 'GET|POST',
            'route' => '/login',
            'controller' => 'login',
            'action' => 'login'
        ],
        [
            'name' => 'logout',
            'method' => 'GET',
            'route' => '/logout',
            'controller' => 'login',
            'action' => 'logout'
        ],
        [
            'name' => 'register',
            'method' => 'POST|GET',
            'route' => '/register',
            'controller' => 'register',
            'action' => 'register'
        ],
        [
            'name' => 'register.activation',
            'method' => 'GET',
            'route' => '/register/activate/[:key]',
            'controller' => 'register',
            'action' => 'activate'
        ],
        [
            'name' => 'register.deny',
            'method' => 'GET',
            'route' => '/register/deny/[:key]',
            'controller' => 'register',
            'action' => 'deny'
        ],
        [
            'name' => 'register.done',
            'method' => 'GET',
            'route' => '/register/done/[i:state]',
            'controller' => 'register',
            'action' => 'done'
        ],
        [
            'name' => 'admin',
            'route' => '/admin',
            'controller' => 'admin',
            'action' => 'index'
        ],
        [
            'name' => 'config',
            'method' => 'GET',
            'route' => '/admin/[a:app_name]/config',
            'controller' => 'config',
            'action' => 'config'
        ],
        [
            'name' => 'config.group',
            'method' => 'GET|POST',
            'route' => '/admin/[a:app_name]/config/[a:group_name]',
            'controller' => 'Config',
            'action' => 'ConfigGroup'
        ],

        // Default generic routes
        [
            'name' => 'main',
            'route' => '/',
            'ctrl' => 'Index'
        ],

        // Generic

        [
            'name' => 'app',
            'route' => '/[a:controller]'
        ],
        [
            'name' => 'action',
            'route' => '/[a:controller]/[a:action]'
        ],
        [
            'name' => 'byid',
            'method' => 'GET|POST',
            'route' => '/[a:controller]/[i:id]/[a:action]'
        ],
        [
            'name' => 'edit',
            'method' => 'POST|GET',
            'route' => '/[a:controller]/[i:id]?/edit',
            'action' => 'Edit'
        ],
        [
            'name' => 'edit_child',
            'method' => 'POST|GET',
            'route' => '/[a:controller]/[i:id]?/edit/of/[i:id_parent]',
            'action' => 'Edit'
        ],
        [
            'name' => 'delete',
            'route' => '/[a:controller]/[i:id]/delete',
            'action' => 'Delete'
        ],
        [
            'name' => 'delete_child',
            'route' => '/[a:controller]/[i:id]?/delete/of/[i:id_parent]',
            'action' => 'Delete'
        ],
        [
            'name' => 'list_by_letter',
            'route' => '/[a:controller]/[a:letter]/list',
            'action' => 'ListByLetter'
        ]
    ];

    public function Start()
    {
        // Add logoff button for logged in users
        if ($this->security->login->loggedIn()) {

            $usermenu = $this->page->menu->createItem('login', $this->security->user->getDisplayname());

            // Show admin menu?
            if ($this->security->user->isAdmin()) {
                $usermenu->createItem('admin', $this->text('menu.admin'), $this->url('admin'));
            }

            $usermenu->createItem('logout', $this->text('menu.logout'), $this->url('logout'));
        }

        // or add login and register buttons
        else {

            $this->page->menu->createItem('register', $this->text('menu.register'), $this->url('register'));
            $this->page->menu->createItem('login', $this->text('menu.login'), $this->url('login'));
        }
    }
}
