<?php
namespace Core\AppsSec\Core\View;

use Core\Lib\Amvc\View;

/**
 * AdminView.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class AdminView extends View
{

    public function Index()
    {
        echo '
        <h1>TekFW Framework Config</h1>
        <div class="row">
            <div class="col-sm-3" id="core-admin-apps">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Applications:</h3>
                    </div>
                    <ul class="list-group">';
        
        foreach ($this->loaded_apps as $app_name => $link) {
            echo '<li class="list-group-item clearfix">', $app_name, ($link ? '<a data-ajax href="' . $link . '" class="btn btn-default btn-xs pull-right"><i class="fa fa-cog"></i></a></li>' : '');
        }
        
        echo '
                    </ul>
                </div>
            </div>
            <div id="core-admin-config" class="col-sm-9"></div>
        </div>';
    }
}

