<?php
namespace AppsSec\Core\View;

use Core\Amvc\View;

/**
 * RegisterView.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
final class RegisterView extends View
{

    public function Done()
    {
        echo '
        <h1>', $this->headline, '</h1>
        <p>', $this->text, '</p>';
    }

    public function Register()
    {
        echo '
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <h1>', $this->headline, '</h1>
                <div class="well">
                    ', $this->form, '
                </div>
            </div>
        </div>';
    }
}

