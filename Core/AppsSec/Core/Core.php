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
        'admin.log'
    ];

    // Apps default config
    protected $config = [

        'site' => [
            'general' => [
                'name' => [
                    'name' => 'name',
                    'validate' => [
                        'empty'
                    ]
                ],
                'url' => [
                    'name' => 'url',
                    'control' => 'url',
                    'validate' => [
                        'empty',
                        'url'
                    ]
                ],
                'webmaster_email' => [
                    'name' => 'webmaster_email',
                    'control' => 'mail',
                    'validate' => [
                        'empty',
                        'email'
                    ]
                ]
            ],
            'language' => [
                'default' => [
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
                    'min_length' => [
                        'name' => 'min_length',
                        'type' => 'int',
                        'control' => 'number',
                        'default' => 8,
                        'validate' => [
                            'empty'
                        ]
                    ],
                    'regexp' => [
                        'name' => 'regexp'
                    ]
                ],
                'password' => [

                    'min_length' => [
                        'name' => 'min_length',
                        'control' => 'number',
                        'default' => 8
                    ],
                    'max_length' => [
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
                    'regexp' => [
                        'name' => 'regexp'
                    ]
                ]
            ],
            'register' => [
                'use_compare_password' => [
                    'name' => 'use_compare_password',
                    'control' => 'switch',
                    'default' => 1
                ]
            ],
            'activation' => [
                'use' => [
                    'name' => 'use',
                    'control' => 'switch',
                    'default' => 1
                ],
                'ttl' => [
                    'name' => 'ttl',
                    'control' => 'number',
                    'default' => 3600
                ],
                'mta' => [
                    'name' => 'mta',
                    'default' => 'default'
                ],
                'from' => [
                    'name' => 'from'
                ],
                'sender' => [
                    'name' => 'name'
                ]
            ],
            'login' => [
                'autologin' => [
                    'name' => 'autologin',
                    'control' => 'switch',
                    'default' => 1
                ],
                'reset_password' => [
                    'name' => 'reset_password',
                    'control' => 'switch',
                    'default' => 1
                ],
                'register' => [
                    'name' => 'register',
                    'control' => 'switch',
                    'default' => 1
                ]
            ],
            'ban' => [
                'tries' => [
                    'name' => 'tries',
                    'control' => 'number',
                    'default' => 5
                ],
                'ttl' => [
                    'log' => [
                        'name' => 'log',
                        'control' => 'number',
                        'default' => 300
                    ],
                    'ban' => [
                        'name' => 'ban',
                        'control' => 'number',
                        'default' => 600
                    ]
                ]
            ],
            'input' => [
                'always_sanitize' => [
                    'name' => 'always_sanitize',
                    'control' => 'switch',
                    'default' => 1
                ]
            ]
        ],

        // Group: Execute
        'execute' => [
            'default' => [
                'app' => [
                    'name' => 'app',
                    'default' => 'Core'
                ],
                'controller' => [
                    'name' => 'controller',
                    'default' => 'Index'
                ],
                'action' => [
                    'name' => 'action',
                    'default' => 'Index'
                ]
            ],
            'content' => [
                'handler' => [
                    'name' => 'handler'
                ]
            ]
        ],
        // Group: JS
        'js' => [
            'general' => [
                'position' => [
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
                'version' => [
                    'name' => 'version',
                    'default' => '2.2.0'
                ],
                'local' => [
                    'name' => 'local',
                    'control' => 'switch',
                    'default' => 1
                ]
            ],
            'style' => [
                'fadeout_time' => [
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
                'version' => [
                    'name' => 'version',
                    'control' => 'input',
                    'default' => '3.3.6',
                    'validate' => [
                        'empty'
                    ]
                ],
                'local' => [
                    'name' => 'local',
                    'control' => 'switch',
                    'default' => 1
                ]
            ],
            'fontawesome' => [
                'version' => [
                    'name' => 'version',
                    'default' => '4.5.0',
                    'validate' => [
                        'empty'
                    ]
                ],
                'local' => [
                    'name' => 'local',
                    'control' => 'switch',
                    'default' => 1
                ]
            ],
            'theme' => [
                'name' => [
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
                'skip_security_check' => [
                    'name' => 'skip_security_check',
                    'control' => 'switch'
                ]
            ],
            'mail' => [
                'use' => [
                    'name' => 'use',
                    'control' => 'switch',
                    'default' => 1
                ],
                'address' => [
                    'name' => 'address'
                ],
                'mta' => [
                    'name' => 'mta',
                    'default' => 'default'
                ]
            ],
            'log' => [
                'use' => [
                    'name' => 'use',
                    'control' => 'switch',
                    'default' => 1
                ],
                'modes' => [
                    'db' => [
                        'name' => 'db',
                        'control' => 'switch',
                        'default' => 1
                    ],
                    'php' => [
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
                'ttl' => [
                    'name' => 'ttl',
                    'control' => 'number',
                    'default' => '3600'
                ],
                'ttl_js' => [
                    'name' => 'ttl_js',
                    'control' => 'number',
                    'default' => '3600'
                ],
                'ttl_css' => [
                    'name' => 'ttl_css',
                    'control' => 'number',
                    'default' => '3600'
                ]
            ],
            'memcache' => [
                'use' => [
                    'name' => 'use',
                    'control' => 'switch'
                ],
                'server' => [
                    'name' => 'server'
                ],
                'port' => [
                    'name' => 'port'
                ]
            ]
        ],

        // Loggingsystem
        'log' => [
            'display' => [
                'entries' => [
                    'name' => 'entries',
                    'control' => 'number',
                    'default' => 20
                ]
            ]
        ],

        // Mailsystem
        'mail' => [
            'general' => [
                'smtpdebug' => [
                    'name' => 'smtpdebug',
                    'control' => 'switch'
                ]
            ],
            'mta' => [
                'default' => [
                    'system' => [
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
                    'host' => [
                        'name' => 'host',
                        'control' => 'mail'
                    ],
                    'port' => [
                        'name' => 'port',
                        'control' => 'number',
                        'default' => 587
                    ],
                    'username' => [
                        'name' => 'username'
                    ],
                    'password' => [
                        'name' => 'password',
                        'control' => 'password'
                    ],
                    'accept_selfsigned' => [
                        'name' => 'accept_selfsigned',
                        'control' => 'switch'
                    ],
                    'protocol' => [
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

        // or add login and register buttons. But not when current user is currently on banlist
        elseif (!$this->security->users->checkBan()) {

            $this->page->menu->createItem('register', $this->text('menu.register'), $this->url('register'));
            $this->page->menu->createItem('login', $this->text('menu.login'), $this->url('login'));
        }
    }
}
