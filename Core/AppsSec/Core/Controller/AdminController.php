<?php
namespace Core\AppsSec\Core\Controller;

use Core\Lib\Amvc\Controller;

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
                            'url' => $this->url('action', [
                                'controller' => 'User',
                                'action' => 'Index'
                            ]),
                            'text' => $this->text('user.plural')
                        ],
                        'groups' => [
                            'url' => $this->url('action', [
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
