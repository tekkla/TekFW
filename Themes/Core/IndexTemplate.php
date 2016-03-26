<?php
namespace Themes\Core;

use Core\Lib\Page\Template;

/**
 * IndexTemplate.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
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
        echo $this->getMessages();

        // Main content
        echo $this->getContent();

        echo $this->getScript('below');

        echo '
        </body>';
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
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="', $this->page->getBaseUrl(), '">', $this->page->getBrand(), '</a>
        </div>
        <div class="collapse navbar-collapse" id="navbar">';

        $service = $this->getMenu('service');

        if ($service) {
            echo '<ul class="nav navbar-nav">';

            foreach ($service->getItems() as $item) {
                echo '<li><a data-ajax href="', $item->getUrl(), '">', $item->getText(), '</a></li>';
            }

            echo '</ul>';
        }

        // Add admin menu and login button
        $register = $this->getMenu('register');
        $login = $this->getMenu('login');

        if ($register || $login) {

            echo '
                <ul class="nav navbar-nav navbar-right">';

            if ($register) {
                echo '<li><a href="', $register->getUrl(), '">', $register->getText(), '</a></li>';
            }

            if ($login) {

                if ($login->isParent()) {
                        echo '
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">', $login->getText(), ' <span class="caret"></span></a>
                            <ul class="dropdown-menu">';

                        foreach ($login->getItems() as $item) {
                            echo '<li><a href="', $item->getUrl(), '">', $item->getText(), '</a></li>';
                        }

                        echo '
                            </ul>
                        </li>';

                }
                else {
                    echo '<li><a href="', $login->getUrl(), '">', $login->getText(), '</a></li>';
                }
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

