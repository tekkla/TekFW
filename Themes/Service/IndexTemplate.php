<?php
namespace Themes\Service;

use Core\Lib\Content\Template;

class IndexTemplate extends Template
{

    protected $layers = [
        'htmlAbove',
        'Header',
        'Body',
        'htmlBelow'
    ];

    public function htmlAbove()
    {
        echo '
        <!DOCTYPE html>

        <html>';
    }

    public function Header()
    {
        echo '
        <head>';

        echo $this->getTitle();
        echo $this->getCss();
        echo $this->getMeta();
        echo $this->getScript('top');

        echo '
        </head>';
    }

    public function Body()
    {
        echo '
        <body>';

            // Navbar
            $this->createMenu();

            // Message container
            echo '<div id="messages" class="container">', $this->getMessages(), '</div>';

            // Main content
            echo '<div class="container" id="content">', $this->getContent(), '</div>',

    		$this->getScript('below'),
	   '</body>';
    }

    public function htmlBelow()
    {
        echo '</html>';
    }

    private function createMenu()
    {
        echo '
<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/">', $this->content->getBrand() , '</a>
        </div>
        <div class="collapse navbar-collapse">';

            $service = $this->getMenu('service');

            if ($service) {
                echo '<ul class="nav navbar-nav">';

                foreach($service->getItems() as $item) {
                    echo '<li><a data-ajax href="',  $item->geTurl(), '">', $item->getText(), '</a></li>';
                }

                echo '</ul>';
            }

            //  Add admin menu and login button
            $admin = $this->getMenu('admin');
            $login = $this->getMenu('login');

            if ($admin || $login) {

                echo'
                <ul class="nav navbar-nav navbar-right">';

                    if ($admin) {
                        echo '
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">', $admin->getText() ,' <span class="caret"></span></a>
                            <ul class="dropdown-menu">';

                            foreach($admin->getItems() as $item) {
                                echo '<li><a href="',  $item->getUrl(), '">', $item->getText(), '</a></li>';
                            }

                            echo '
                            </ul>
                        </li>';
                    }

                    if ($login) {
                        echo '<li><a href="',  $login->getUrl(), '">', $login->getText(), '</a></li>';
                    }

                echo '
                </ul>';
            }

            echo '
        </div>
    </div>
</nav>';

    }
}

