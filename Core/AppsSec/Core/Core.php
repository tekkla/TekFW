<?php
namespace Core\AppsSec\Core;

use Core\Lib\Amvc\App;

/**
 * Core App
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
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
                    1 => 'horizontal',
                ],
                0
            ],
            'default' => 0
        ],

        // Security
        'min_login_length' => [
            'group' => 'security',
            'control' => 'number',
            'default' => 8
        ],
        'max_login_length' => [
            'group' => 'security',
            'control' => 'number',
            'default' => 5
        ],
        'min_password_length' => [
            'group' => 'security',
            'control' => 'number',
            'default' => 8
        ],
        'max_password_length' => [
            'group' => 'security',
            'control' => 'number',
            'default' => 50,
            'validate' => [
                'required',
                [
                    'range',
                    [8,
                    100]
                ]
            ]
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
            'control' => 'input',
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
        'js_modernizr' => [
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

        // Group: Minify
        'css_minify' => [
            'group' => 'minify',
            'control' => 'switch',
            'default' => 0
        ],
        'js_minify' => [
            'group' => 'minify',
            'control' => 'switch',
            'default' => 0
        ],

        // Bootstrap
        'bootstrap_version' => [
            'group' => 'style',
            'control' => 'input',
            'default' => '3.1.1',
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
            'control' => 'input',
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

    protected function addMenuItems()
    {
        if ($this->security->checkAccess('core_admin')) {
            $this->content->navbar->createRootItem('admin', $this->txt('admin'), $this->router->url('core_admin'));
        }

        if ($this->security->loggedIn()) {
            $text = $this->txt('logout');
            $route = 'core_logout';
        }
        else {
            $text = $this->txt('login');
            $route = 'core_login';
        }

        $this->content->navbar->createRootItem('login', $text, $this->router->url($route));
    }
}
