<?php
namespace Core\AppsSec\Core\Model;

use Core\Lib\Amvc\Model;
use Core\Lib\Security\Group;

/**
 * GroupModel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class GroupModel extends Model
{

    public function getGroup($id_group = null)
    {
        $group = [
            'title' => '',
            'display_name' => '',
            'User' => [],
            'GroupPermissions' => [],
        ];

        if ($id_group) {
            $group = $this->security->group->getGroupById($id_group);
            $group['User'] = $this->getModel('User')->loadUsersByGroupId($id_group);
        }

        return $group;
    }

    public function getGroups($skip_guest = false)
    {
        $data = $this->security->group->getGroups(false, $skip_guest);

        foreach ($data as &$app_groups) {

            foreach ($app_groups as $id_group => &$group) {

                $group['link'] = $this->url('byid', [
                    'controller' => 'Group',
                    'action' => 'Detail',
                    'id' => $group['id_group']
                ]);

                $group['User'] = $this->getModel('User')->loadUsersByGroupId($id_group);
            }
        }

        return $data;
    }


    public function save($data) {


            var_dump($data);

    }
}



