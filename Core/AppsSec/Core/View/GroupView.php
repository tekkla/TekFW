<?php
namespace Core\AppsSec\Core\View;

use Core\Lib\Amvc\View;

/**
 * GroupView.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class GroupView extends View
{

    public function Index()
    {
        echo '
        <h1>', $this->headline, '</h1>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th class="col-xs-2">', $this->id_group, '</th>
                    <th class="col-xs-2">', $this->group, '</th>
                    <th>', $this->members, '</th>
                </tr>
            </thead>';

        foreach ($this->grouplist as $app => $groups) {
            echo '
            <thead>
                <tr>
                    <th colspan="3">', $app, '</th>
                </tr>
            </thead>
            <tbody>';

            foreach ($groups as $id_group => $group) {

                echo '
                <tr data-ajax data-url="', $group['link'] , '">
                    <td>', $id_group, '</td>
                    <td>', $group['display_name'], '</td>
                    <td>';

                echo $this->generateuserlist($group['users']);

                echo '
                    </td>
                </tr>';
            }

            echo '
            </tbody>';
        }

        echo '
        </table>';
    }

    private function generateuserlist($users)
    {
        $pieces = [];

        foreach ($users as $user) {
            $pieces[] = '<a data-ajax href="' . $user['link'] . '">' . $this->html($user['display_name']) . '</a>';
        }

        return implode(' ', $pieces);
    }
}

