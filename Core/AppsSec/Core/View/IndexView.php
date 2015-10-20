<?php
namespace Core\AppsSec\Core\View;

use Core\Lib\Amvc\View;

/**
 * IndexView.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
final class IndexView extends View
{

    public function Index()
    {
        echo '<div class="row"><div class="col-sm-12">Default Index View</div></div>';
    }
}

