<?php

namespace Entities\Teacher;

use Entities\Template\AbstractView;

use \App;
use System\DbAdapter;

class View extends AbstractView
{
    public function indexAction()
    {
        $this->setTitle($this->tr('Teachers'));
        $id = $this->getRequest()->getGET($this->getModel()->getEntityPrimaryKey());
        if ($id) {
            $this->getModel()->load($id);
        }
    }

    /**
     * Return collection of all existing teachers
     *
     * @return \Entities\Template\AbstractCollection
     */
    public function getTeachersList()
    {
        $teachers = $this->getModel()->getCollection();
        return $teachers;
    }
}