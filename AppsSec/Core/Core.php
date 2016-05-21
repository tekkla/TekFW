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
        self::LANGUAGE,
    ];

    protected $permissions = [
        'admin.groups',
        'admin.user',
        'admin.log'
    ];

    // Apps default config
    protected $config = [
        'settings' => [
            'headline' => 'Core Framework Config'
        ],
        'groups' => [
            'site' => [
                'groups' => [
                    'general' => [
                        'controls' => [
                            'name' => [
                                'validate' => [
                                    'empty'
                                ],
                                'default' => 'MySite'
                            ],
                            'webmaster_email' => [
                                'control' => 'mail',
                                'validate' => [
                                    'empty',
                                    'email'
                                ]
                            ]
                        ]
                    ],
                    'language' => [
                        'controls' => [
                            'default' => [
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
                    ]
                ]
            ],
            'security' => [
                'groups' => [
                    'encrypt' => [
                        'controls' => [
                            'pepper' => [
                                'default' => '@m@rschH@ngtDerH@mmer1234',
                                'validate' => [
                                    'empty'
                                ]
                            ]
                        ]
                    ],
                    'login' => [
                        'groups' => [
                            'autologin' => [
                                'controls' => [
                                    'active' => [
                                        'control' => 'switch',
                                        'default' => 1
                                    ],
                                    'expires_after' => [
                                        'control' => 'number',
                                        'default' => 30
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'ban' => [
                        'controls' => [
                            'tries' => [
                                'control' => 'number',
                                'default' => 5
                            ]
                        ],
                        'groups' => [
                            'ttl' => [
                                'controls' => [
                                    'log' => [
                                        'control' => 'number',
                                        'default' => 300
                                    ],
                                    'ban' => [
                                        'control' => 'number',
                                        'default' => 600
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'user' => [
                'groups' => [
                    'username' => [
                        'controls' => [
                            'min_length' => [
                                'type' => 'int',
                                'control' => 'number',
                                'default' => 8,
                                'validate' => [
                                    'empty',
                                    [
                                        'min',
                                        5
                                    ]
                                ]
                            ],
                            'regexp' => [
                                'control' => [
                                    'textarea',
                                    [
                                        'rows' => 2
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'password' => [
                        'controls' => [
                            'min_length' => [
                                'control' => 'number',
                                'default' => 8,
                                'validate' => [
                                    'empty',
                                    [
                                        'min',
                                        8
                                    ]
                                ]
                            ],
                            'max_length' => [
                                'control' => 'number',
                                'default' => 1024,
                                'validate' => [
                                    'empty',
                                    [
                                        'max',
                                        4096
                                    ]
                                ]
                            ],
                            'regexp' => [
                                'control' => [
                                    'textarea',
                                    [
                                        'rows' => 2
                                    ]
                                ]
                            ],
                            'reactivate_after_password_change' => [
                                'default' => 0,
                                'control' => 'switch',
                                'validate' => [
                                    [
                                        'enum',
                                        [
                                            0,
                                            1
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'register' => [
                        'controls' => [
                            'use_compare_password' => [
                                'control' => 'switch',
                                'default' => 1
                            ]
                        ]
                    ],
                    'activation' => [
                        'controls' => [
                            'use' => [
                                'control' => 'select',
                                'data' => [
                                    'type' => 'array',
                                    'source' => [
                                        0 => 'instant',
                                        1 => 'mail',
                                        2 => 'useradmin'
                                    ],
                                    'index' => 0
                                ],
                                'default' => 0
                            ]
                        ],
                        'groups' => [
                            'mail' => [
                                'settings' => [
                                    'require' => [
                                        'config' => [
                                            [
                                                'core',
                                                'user.activation.use',
                                                1
                                            ]
                                        ]
                                    ]
                                ],
                                'controls' => [
                                    'ttl' => [
                                        'control' => 'number',
                                        'default' => 3600
                                    ],
                                    'mta' => [
                                        'control' => [
                                            'select',
                                            [
                                                'required' => false
                                            ]
                                        ],
                                        'data' => [
                                            'type' => 'model',
                                            'source' => [
                                                'app' => 'core',
                                                'model' => 'mta',
                                                'action' => 'getMtaIdTitleList'
                                            ],
                                            'index' => 0
                                        ]
                                    ],
                                    'from' => [
                                        'control' => 'mail',
                                        'validate' => [
                                            'email'
                                        ]
                                    ],
                                    'name' => []
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // Group: Execute
            'home' => [
                'groups' => [
                    'guest' => [
                        'controls' => [
                            'route' => [
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
                                'control' => [
                                    'textarea',
                                    [
                                        'rows' => 4
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'user' => [
                        'controls' => [
                            'route' => [
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
                                'control' => 'textarea'
                            ]
                        ]
                    ]
                ]
            ],

            // Group: JS
            'js' => [
                'groups' => [
                    'general' => [
                        'controls' => [
                            'position' => [
                                'control' => 'select',
                                'data' => [
                                    'type' => 'array',
                                    'source' => [
                                        't' => 'Top',
                                        'b' => 'Bottom'
                                    ],
                                    'index' => 0
                                ],
                                'default' => 't',
                                'validate' => [
                                    [
                                        'enum',
                                        [
                                            't',
                                            'b'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],

                    'jquery' => [
                        'controls' => [
                            'version' => [
                                'default' => '2.2.0'
                            ],
                            'local' => [
                                'control' => 'switch',
                                'default' => 1
                            ]
                        ]
                    ],
                    'style' => [
                        'controls' => [
                            'fadeout_time' => [
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
                    ]
                ]
            ],
            'style' => [
                'groups' => [
                    // Bootstrap
                    'bootstrap' => [
                        'controls' => [
                            'version' => [
                                'control' => 'input',
                                'default' => '3.3.6',
                                'validate' => [
                                    'empty'
                                ]
                            ],
                            'local' => [
                                'control' => 'switch',
                                'default' => 1
                            ]
                        ]
                    ],
                    'fontawesome' => [
                        'controls' => [
                            'version' => [
                                'default' => '4.5.0',
                                'validate' => [
                                    'empty'
                                ]
                            ],
                            'local' => [
                                'control' => 'switch',
                                'default' => 1
                            ]
                        ]
                    ],
                    'theme' => [
                        'controls' => [
                            'name' => [
                                'control' => 'text',
                                'default' => 'Core',
                                'validate' => [
                                    'empty'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // Error handling
            'error' => [
                'groups' => [
                    'display' => [
                        'controls' => [
                            'skip_security_check' => [
                                'control' => 'switch'
                            ]
                        ]
                    ],
                    'mail' => [
                        'controls' => [
                            'use' => [
                                'control' => 'switch',
                                'default' => 1
                            ]
                        ],
                        'groups' => [
                            'mta' => [
                                'controls' => [
                                    'recipient' => [],
                                    'use' => [
                                        'control' => 'select',
                                        'data' => [
                                            'type' => 'model',
                                            'source' => [
                                                'app' => 'core',
                                                'model' => 'mta',
                                                'action' => 'getMtaIdTitleList'
                                            ],
                                            'index' => 0
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'log' => [
                        'controls' => [
                            'use' => [
                                'control' => 'switch',
                                'default' => 1
                            ]
                        ],
                        'groups' => [
                            'modes' => [
                                'controls' => [
                                    'db' => [
                                        'control' => 'switch',
                                        'default' => 1
                                    ],
                                    'php' => [
                                        'control' => 'switch',
                                        'default' => 1
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // Caching
            'cache' => [
                'groups' => [
                    'ttl' => [
                        'controls' => [
                            'js' => [
                                'control' => 'number',
                                'default' => '3600'
                            ],
                            'css' => [
                                'control' => 'number',
                                'default' => '3600'
                            ]
                        ]
                    ]
                ]
            ],

            // Loggingsystem
            'log' => [
                'groups' => [
                    'display' => [
                        'controls' => [
                            'entries' => [
                                'control' => 'number',
                                'default' => 20
                            ]
                        ]
                    ]
                ]
            ],

            // Mailsystem
            'mail' => [
                'groups' => [
                    'general' => [
                        'controls' => [
                            'smtpdebug' => [
                                'name' => 'smtpdebug',
                                'control' => 'select',
                                'data' => [
                                    'type' => 'array',
                                    'source' => [
                                        0 => 'off',
                                        1 => 1,
                                        2 => 2,
                                        3 => 3,
                                        4 => 4,
                                        5 => 5,
                                        6 => 6
                                    ],
                                    'index' => 0
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    // Apps routes
    protected $routes = [
        'index' => [
            'route' => '../',
            'target' => [
                'controller' => 'index',
                'action' => 'index'
            ]
        ],
        'login' => [
            'route' => '/[login|logout:action]',
            'method' => 'POST|GET',
            'target' => [
                'controller' => 'login'
            ]
        ],
        'register' => [
            'method' => 'POST|GET',
            'route' => '/register',
            'target' => [
                'controller' => 'user',
                'action' => 'register'
            ]
        ],
        'activate' => [
            'route' => '/[activate|deny:action]/[:key]',
            'target' => [
                'controller' => 'user'
            ]
        ],
        'admin' => [
            'method' => 'GET',
            'route' => '/admin',
            'target' => [
                'controller' => 'admin',
                'action' => 'admin'
            ]
        ],
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

            // Set home url
            $type = 'user';

            $usermenu = $this->page->menu->createItem('login', $this->security->user->getDisplayname());

            // Show admin menu?
            if ($this->security->user->isAdmin()) {
                $usermenu->createItem('admin', $this->text->get('menu.admin'), $this->url('admin'));
            }

            $usermenu->createItem('logout', $this->text->get('menu.logout'), $this->url('login', [
                'action' => 'logout'
            ]));
        }

        // or add login and register buttons. But not when current user is currently on banlist
        elseif (!$this->security->users->checkBan()) {

            $type = 'guest';

            $this->page->menu->createItem('register', $this->text->get('menu.register'), $this->url('register'));
            $this->page->menu->createItem('login', $this->text->get('menu.login'), $this->url('login', [
                'action' => 'login'
            ]));
        }

        $route = $this->cfg->Core->get('home.' . $type . '.route');
        $params = parse_ini_string($this->cfg->Core->get('home.' . $type . '.params'));
        $url = $this->url($route, $params);

        $this->page->setHome($url);
        $this->cfg->Core->set('url.home', $url);
    }

    protected function Init()
    {
        $this->initJsAssets();

        $this->initCssAssets();
    }

    private function initJsAssets()
    {

        if (!$this->router->isAjax()) {

        /* @var $ah \Core\Asset\Javascript\JavascriptHandler */
        $ah = $this->di->get('core.asset')->getAssetHandler('js');

        // Theme name
        $theme = $this->cfg->Core->get('style.theme.name');

        // jQuery version
        $version = $this->cfg->Core->get('js.jquery.version');

        // Add local jQeury file or the one from CDN
        $file = '/' . $theme . '/js/jquery-' . $version . '.js';

        // Files to bottom or to top?
        $defer = $this->cfg->Core->get('js.general.position') == 'top' ? false : true;


        if ($this->cfg->Core->get('js.jquery.local') && file_exists(THEMESDIR . $file)) {
            $ao = $ah->createFile(THEMESURL . $file, $defer);
        }
        else {
            $ao = $ah->createFile('https://code.jquery.com/jquery-' . $version . '.min.js', $defer, false, false);
        }

        $ao->setId('jquery-' . $version);

        // Bootstrap Version
        $version = $this->cfg->Core->get('style.bootstrap.version');

        // Add Bootstrap javascript from local or cdn
        $file = '/' . $theme . '/js/bootstrap-' . $version . '.js';

        if ($this->cfg->Core->get('style.bootstrap.local') && file_exists(THEMESDIR . $file)) {
            $ao = $ah->createFile(THEMESURL . $file, $defer);
        }
        else {
            $ao = $ah->createFile('https://maxcdn.bootstrapcdn.com/bootstrap/' . $version . '/js/bootstrap.min.js', $defer, false, false);
        }

        $ao->setId('bootstrap-' . $version);

        // Add plugins file
        $ao = $ah->createFile($this->cfg->Core->get('url.js') . '/plugins.js', $defer);
        $ao->setId('core-plugins');

        // Add global fadeout time var set in config
        $ah->createVariable('fadeout_time', $this->cfg->Core->get('js.style.fadeout_time'), false, $defer);

        // Add framework js
        $ao = $ah->createFile($this->cfg->Core->get('url.js') . '/framework.js', $defer);
        $ao->setId('core-framework');

        }
    }

    private function initCssAssets()
    {



        /* @var $ah \Core\Asset\Css\CssHandler */
        $ah = $this->di->get('core.asset')->getAssetHandler('css');

        // Theme name
        $theme = $this->cfg->Core->get('style.theme.name');

        // Bootstrap version from config
        $version = $this->cfg->Core->get('style.bootstrap.version');

        // Core and theme file
        $file = '/' . $theme . '/css/bootstrap-' . $version . '.css';

        // Add existing local user/theme related bootstrap file or load it from cdn
        if ($this->cfg->Core->get('style.bootstrap.local') && file_exists(THEMESDIR . $file)) {
            $ao = $ah->createLink(THEMESURL . $file);
        }
        else {
            // Add bootstrap main css file from cdn
            $ao = $ah->createLink('https://maxcdn.bootstrapcdn.com/bootstrap/' . $version . '/css/bootstrap.min.css');
        }

        $ao->setId('bootstrap-' . $version);

        // Fontawesome version
        $version = $this->cfg->Core->get('style.fontawesome.version');

        // Fontawesome file
        $file = '/' . $theme . '/css/font-awesome-' . $version . '.css';

        // Add existing font-awesome font icon css file or load it from cdn
        if ($this->cfg->Core->get('style.fontawesome.local') && file_exists(THEMESDIR . $file)) {
            $ao = $ah->createLink(THEMESURL . $file);
        }
        else {
            $ao = $ah->createLink('https://maxcdn.bootstrapcdn.com/font-awesome/' . $version . '/css/font-awesome.min.css');
        }
        $ao->setId('font-awesome-' . $version);

        // Add general TekFW css file
        $file = '/' . $theme . '/css/Core.css';

        $ao = $ah->createLink(THEMESURL . $file);
        $ao->setId('core-css');
    }
}
