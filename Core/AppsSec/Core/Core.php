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

        // Group: Global
        'default_action' => [
            'group' => 'global',
            'control' => 'input',
            'default' => 'forum'
        ],
        'default_app' => [
            'group' => 'global',
            'control' => 'input'
        ],
        'default_ctrl' => [
            'group' => 'global',
            'control' => 'input'
        ],
        'content_handler' => [
            'group' => 'global',
            'control' => 'input'
        ],
        'menu_handler' => [
            'group' => 'global',
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
            'translate' => false
        ],
        'js_html5shim' => [
            'group' => 'js',
            'control' => 'switch',
            'default' => 0
        ],
        'js_modernizr' => [
            'group' => 'js',
            'control' => 'switch',
            'default' => 0
        ],
        'js_selectivizr' => [
            'group' => 'js',
            'control' => 'switch',
            'default' => 0
        ],
        'js_fadeout_time' => [
            'group' => 'js',
            'control' => 'number',
            'default' => 5000
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
            'translate' => false
        ],

        'fontawesome_version' => [
            'group' => 'style',
            'control' => 'input',
            'default' => '4.0.3',
            'translate' => false
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

        // Url related
        'url_seo' => [
            'group' => 'url',
            'control' => 'switch',
            'default' => 0
        ],

        // Display
        'theme' => [
            'group' => 'display',
            'control' => 'text',
            'default' => 'Core'
        ],

        // Security
        'min_login_lenght' => [
            'group' => 'security',
            'control' => 'number',
            'default' => 8
        ],
        'max_login_lenght' => [
            'group' => 'security',
            'control' => 'number',
            'default' => 5
        ],
        'min_password_lenght' => [
            'group' => 'security',
            'control' => 'number',
            'default' => 8
        ],
        'min_password_lenght' => [
            'group' => 'security',
            'control' => 'number',
            'default' => 50
        ]
    ];

    // Apps routes
    protected $routes = [
        [
            'name' => 'index',
            'route' => '../',
            'ctrl' => 'Index',
            'action' => 'Index'
        ],
        [
            'name' => 'login',
            'method' => 'GET|POST',
            'route' => '../login',
            'ctrl' => 'Security',
            'action' => 'Login'
        ],
        [
            'name' => 'logout',
            'method' => 'GET',
            'route' => '../logout',
            'ctrl' => 'Security',
            'action' => 'Logout'
        ],
        [
            'name' => 'admin',
            'route' => '../admin',
            'ctrl' => 'admin',
            'action' => 'index'
        ],
        [
            'name' => 'install',
            'route' => '../admin/[a:app_name]/install',
            'ctrl' => 'config',
            'action' => 'install'
        ],
        [
            'name' => 'remove',
            'route' => '../admin/[a:app_name]/remove',
            'ctrl' => 'config',
            'action' => 'remove'
        ],
        [
            'name' => 'config',
            'method' => 'GET|POST',
            'route' => '../admin/[a:app_name]/config',
            'ctrl' => 'config',
            'action' => 'config'
        ],
        [
            'name' => 'reconfig',
            'route' => '../admin/[a:app_name]/reconfig',
            'ctrl' => 'config',
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
        } else {
            $text = $this->txt('login');
            $route = 'core_login';
        }

        $this->content->navbar->createRootItem('login', $text, $this->router->url($route));
    }
}
