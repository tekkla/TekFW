<?php
namespace Core\AppsSec\Core\View;

use Core\Lib\Amvc\View;

/**
 * ConfigView.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
final class ConfigView extends View
{

    public function Config()
    {
        echo '<h1>' . $this->icon . '&nbsp;' . $this->app_name . '</h1>' . $this->form;
    }
}

