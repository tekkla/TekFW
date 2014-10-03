<?php
namespace Core\AppsSec\Doc\Model;

use Core\Lib\Amvc\Model;

final class GroupModel extends Model
{

    protected $tbl = 'app_doc_groups';

    protected $alias = 'groups';

    protected $pk = 'id_group';

    public function getGroups()
    {
        return $this->read(array(
            'type' => '*',
            'order' => 'position'
        ), 'loadChilds');
    }

    public function loadChilds(&$group)
    {
        $groupdocs = $this->getModel('Document')->getGroupDocs($groupid_group);
        
        return $group;
    }

    public function getGroupSelection()
    {
        return $this->read(array(
            'type' => '2col',
            'field' => array(
                'id_group',
                'title'
            ),
            'order' => 'position'
        ));
    }
}


