<?php
namespace AppsSec\Core\Controller;

use Core\Amvc\Controller;

/**
 * AdminController.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
final class AdminController extends Controller
{

    public function Index()
    {
        $this->setVar([
            'loaded_apps' => $this->model->getApplist(),
            'logs' => $this->getController('Log')
                ->run('Index'),

            // Links to users and permissions
            'menu' => [
                'users' => [
                    'title' => $this->text('admin.menu.users'),
                    'links' => [
                        'users' => [
                            'url' => $this->url('generic.action', [
                                'app' => 'core',
                                'controller' => 'user',
                                'action' => 'index'
                            ]),
                            'text' => $this->text('user.plural')
                        ],
                        'groups' => [
                            'url' => $this->url('generic.action', [
                                'app' => 'core',
                                'controller' => 'Group',
                                'action' => 'Index'
                            ]),
                            'text' => $this->text('group.plural')
                        ]
                    ]
                ]
            ]
        ]);
    }
}
