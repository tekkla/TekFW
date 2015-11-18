<?php
namespace Core\AppsSec\Core;

use Core\Lib\Amvc\App;

/**
 * Core.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
final class Core extends App
{
    // Flag as secured app
    protected $secure = true;

    // Uses language system
    protected $language = true;

    // Apps default config
    protected $config = [

        // Config
        'config_display_style' => [
            'group' => 'config',
            'control' => 'select',
            'data' => [
                'array',
                [
                    0 => 'top down',
                    1 => 'horizontal'
                ],
                0
            ],
            'default' => 0
        ],

        // Security
        'min_username_length' => [
            'group' => 'security',
            'control' => 'number',
            'default' => 8
        ],
        'username_regexp' => [
            'group' => 'security',
            'control' => 'input'
        ],
        'min_password_length' => [
            'group' => 'security',
            'control' => 'number',
            'default' => 8
        ],
        'max_password_length' => [
            'group' => 'security',
            'control' => 'number',
            'default' => 4096,
            'validate' => [
                'required',
                [
                    'max',
                    [
                        8,
                        4096
                    ]
                ]
            ]
        ],
        'password_regexp' => [
            'group' => 'security',
            'control' => 'input'
        ],
        'autologin' => [
            'group' => 'security',
            'control' => 'switch',
            'default' => 1
        ],
        'tries_before_ban' => [
            'group' => 'security',
            'control' => 'number',
            'default' => 5
        ],

        // Group: Execute

        'default_app' => [
            'group' => 'execute',
            'control' => 'input'
        ],
        'default_controller' => [
            'group' => 'execute',
            'control' => 'input'
        ],
        'default_action' => [
            'group' => 'execute',
            'control' => 'input'
        ],

        // Group: JS
        'js_default_position' => [
            'group' => 'js',
            'control' => 'select',
            'data' => [
                'array',
                [
                    't' => 'Top',
                    'b' => 'Bottom'
                ],
                0
            ],
            'default' => 'top',
            'translate' => 'false'
        ],
        'jquery_version' => [
            'group' => 'js',
            'control' => 'input',
            'default' => '1.11.1',
            'translate' => false,
            'validate' => [
                'required'
            ]
        ],
        'jquery_use_local' => [
            'group' => 'js',
            'control' => 'switch',
            'default' => 0
        ],
        'js_fadeout_time' => [
            'group' => 'js',
            'control' => [
                'number',
                [
                    'min' => 100
                ]
            ],
            'default' => 5000,
            'validate' => [
                'required',
                [
                    'min',
                    100
                ]
            ]
        ],

        // Bootstrap
        'bootstrap_version' => [
            'group' => 'style',
            'control' => 'input',
            'default' => '3.3.5',
            'translate' => false,
            'validate' => [
                'required'
            ]
        ],
        'bootstrap_use_local' => [
            'group' => 'style',
            'control' => 'switch',
            'default' => 0,
            'type' => 'int'
        ],
        'fontawesome_version' => [
            'group' => 'style',
            'control' => 'input',
            'default' => '4.0.3',
            'translate' => false,
            'validate' => [
                'required'
            ]
        ],
        'fontawesome_use_local' => [
            'group' => 'style',
            'control' => 'switch',
            'default' => 0,
            'type' => 'int'
        ],
        'theme' => [
            'group' => 'style',
            'control' => 'text',
            'default' => 'Core',
            'validate' => [
                'required'
            ]
        ],

        // Error logger
        'error_logger' => [
            'group' => 'error',
            'control' => 'switch',
            'default' => 1
        ],
        'error_to_db' => [
            'group' => 'error',
            'control' => 'switch',
            'default' => 1
        ],
        'error_to_mail' => [
            'group' => 'error',
            'control' => 'switch',
            'default' => 1
        ],
        'error_to_mail_address' => [
            'group' => 'error',
            'control' => 'input'
        ],
        'error_to_log' => [
            'group' => 'error',
            'control' => 'switch',
            'default' => 1
        ],
        'skip_security_check' => [
            'group' => 'error',
            'control' => 'switch',
            'default' => 0
        ],

        // Logging
        'log' => [
            'group' => 'logging',
            'control' => 'switch',
            'default' => 0
        ],
        'show_log_output' => [
            'group' => 'logging',
            'control' => 'switch',
            'default' => 1
        ],
        'log_db' => [
            'group' => 'logging',
            'control' => 'switch',
            'default' => 1
        ],
        'log_app' => [
            'group' => 'logging',
            'control' => 'switch',
            'default' => 1
        ],
        'log_handler' => [
            'group' => 'logging',
            'control' => 'select',
            'data' => [
                'array',
                [
                    'page' => 'Page',
                    'fire' => 'FirePHP'
                ],
                0
            ],
            'default' => 'page',
            'translate' => false
        ],

        // Caching
        'cache_ttl' => [
            'group' => 'cache',
            'control' => 'text',
            'default' => '3600'
        ],
        'cache_ttl_js' => [
            'group' => 'cache',
            'control' => 'text',
            'default' => '3600'
        ],
        'cache_ttl_css' => [
            'group' => 'cache',
            'control' => 'text',
            'default' => '3600'
        ],
        'cache_memcache_use' => [
            'group' => 'cache',
            'control' => 'switch',
            'default' => 0,
        ],
        'cache_memcache_server' => [
            'group' => 'cache',
            'control' => 'text',
        ],
        'cache_memcache_port' => [
            'group' => 'cache',
            'control' => 'text',
        ],
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
            'route' => '../login',
            'controller' => 'Security',
            'action' => 'Login'
        ],
        [
            'name' => 'logout',
            'method' => 'GET',
            'route' => '../logout',
            'controller' => 'Security',
            'action' => 'Logout'
        ],
        [
            'name' => 'admin',
            'route' => '../admin',
            'controller' => 'admin',
            'action' => 'index'
        ],
        [
            'name' => 'install',
            'route' => '../admin/[a:app_name]/install',
            'controller' => 'config',
            'action' => 'install'
        ],
        [
            'name' => 'remove',
            'route' => '../admin/[a:app_name]/remove',
            'controller' => 'config',
            'action' => 'remove'
        ],
        [
            'name' => 'config',
            'method' => 'GET|POST',
            'route' => '../admin/[a:app_name]/config',
            'controller' => 'config',
            'action' => 'config'
        ],
        [
            'name' => 'reconfig',
            'route' => '../admin/[a:app_name]/reconfig',
            'controller' => 'config',
            'action' => 'reconfigure'
        ]
    ];

    public function Start()
    {
$this->debugFbLog($this->security->checkAccess('core_admin'));

        if ($this->security->checkAccess('core_admin')) {

            $root = $this->content->menu->createItem('admin', $this->txt('admin'));

            $apps = $this->di->get('core.amvc.creator')->getLoadedApps();

            foreach ($apps as $app) {
                $root->createItem('admin_app_' . $app, $app, $this->url('config', [
                    'app_name' => $app
                ]));
            }
        }

        $key = $this->security->loggedIn() ? 'logout' : 'login';

        $this->content->menu->createItem('login', $this->txt($key), $this->url($key));
    }
}
