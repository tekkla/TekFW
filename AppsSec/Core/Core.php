<?php
namespace AppsSec\Core;

use Core\Amvc\App;

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
    protected $flags = [
        self::SECURE,
        self::LANGUAGE
    ];

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
                    ],
                    'default' => 'MySite'
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
                        'type' => 'array',
                        'source' => [
                            'en',
                            'de'
                        ],
                        'index' => 1
                    ],
                    'default' => 'en'
                ]
            ]
        ],
        'security' => [
            'encrypt' => [
                'pepper' => [
                    'name' => 'pepper',
                    'default' => '@m@rschH@ngtDerH@mmer1234',
                    'validate' => [
                        'empty'
                    ]
                ]
            ],
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

        'login' => [
            'autologin' => [
                'active' => [
                    'name' => 'active',
                    'control' => 'switch',
                    'default' => 1
                ],
                'expires_after' => [
                    'name' => 'expires_after',
                    'control' => 'number',
                    'default' => 30
                ]
            ]
        ],

        // Group: Execute
        'home' => [
            'guest' => [
                'route' => [
                    'name' => 'route',
                    'control' => 'select',
                    'data' => [
                        'type' => 'model',
                        'source' => [
                            'app' => 'core',
                            'model' => 'config',
                            'action' => 'getAllRoutes'
                        ],
                        'index' => 0,
                    ],
                ],
                'params' => [
                    'name' => 'params',
                    'control' => 'textarea'
                ]
            ],
            'user' => [
                'route' => [
                    'name' => 'route',
                    'control' => 'select',
                    'data' => [
                        'type' => 'model',
                        'source' => [
                            'app' => 'core',
                            'model' => 'config',
                            'action' => 'getAllRoutes'
                        ],
                        'index' => 0
                    ]
                ],
                'params' => [
                    'name' => 'params',
                    'control' => 'textarea'
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
                        'type' => 'array',
                        'source' => [
                            't' => 'Top',
                            'b' => 'Bottom'
                        ],
                        'index' => 0
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
            ]
        ]
    ];

    // Apps routes
    protected $routes = [
        'index' => [
            'route' => '../'
        ],
        'login' => [
            'method' => 'GET|POST'
        ],
        'login.logout' => [
            'route' => '/logout',
        ],
        'register' => [
            'method' => 'POST|GET'
        ],
        'register.activate' => [
            'route' => '/register/activate/[:key]'
        ],
        'register.deny' => [
            'route' => '/register/deny/[:key]'
        ],
        'register.done' => [
            'route' => '/register/done/[i:state]'
        ],
        'admin' => [],
        'config' => [
            'route' => '/admin/[mvc:app_name]/config'
        ],
        'config.group' => [
            'method' => 'GET|POST',
            'route' => '/admin/[mvc:app_name]/config/[a:group_name]'
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

            $usermenu->createItem('logout', $this->text('menu.logout'), $this->url('login.logout'));
        }

        // or add login and register buttons. But not when current user is currently on banlist
        elseif (! $this->security->users->checkBan()) {

            $this->page->menu->createItem('register', $this->text('menu.register'), $this->url('register'));
            $this->page->menu->createItem('login', $this->text('menu.login'), $this->url('login'));
        }
    }
}
