<?php
namespace Core\AppsSec\Core\View;

use Core\Lib\Amvc\View;

/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 *
 */
final class SecurityView extends View
{

    public function Login()
    {
        echo '
        <div class="row">
            <div class="col-md-4 col-md-offset-4">', $this->form , '</div>
        </div>';
    }

    public function AlreadyLoggedIn()
    {
        echo '
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <strong>', $this->loggedin, '</strong>
            </div>
        </div>';
    }
}

