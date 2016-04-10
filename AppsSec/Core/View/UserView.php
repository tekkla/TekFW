<?php
namespace AppsSec\Core\View;

use Core\Amvc\View;

/**
 * UserView.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class UserView extends View
{

    public function Userlist()
    {
        echo '
        <h1>', $this->text['headline'], '</h1>
        <table class="table table-condensed table-striped">
            <thead>
                <tr>
                    <th>', $this->text['username'], '</th>
                    <th>', $this->text['display_name'], '</th>
                <tr>
            </thead>
            <tbody>';

            foreach($this->userlist as $user) {

                echo '
                <tr data-ajax data-url="', $user['link'], '">
                    <td>', $user['username'], '<td>
                    <td>', $user['display_name'], '<td>
                </tr>';

            }

            echo '
            </tbody>
        <table>';
    }

    public function Edit()
    {
        echo $this->form;
    }
}
