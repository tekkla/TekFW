<?php
namespace Core\AppsSec\Doc\Model;

use Core\Lib\Amvc\Model;
use Core\Lib\Data\Data;

final class DocumentModel extends Model
{

    protected $tbl = 'app_doc_documents';

    protected $alias = 'doc';

    protected $pk = 'id_document';

    public function getGroupDocs($id_group)
    {
        return $this->read(array(
            'type' => '*',
            'filter' => 'id_group={int:id_group}',
            'params' => array(
                'id_group' => $id_group
            ),
            'order' => 'position'
        ));
    }

    public function getDoc($id_document = null)
    {
        if (isset($id_document))
            $this->find($id_document);
        else
            $this->data = new Data(array(
                'headline' => 'New document',
                'content' => '',
                'position' => 0,
                'id_group' => 0
            ));

	 =>  
        return $this->data;
    }
}

