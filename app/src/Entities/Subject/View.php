<?php

namespace Entities\Subject;

use Entities\Template\AbstractView;

use \App;
use System\DbAdapter;

class View extends AbstractView
{
    public function indexAction()
    {
        $this->setTitle($this->tr('Subjects'));
        $id = $this->getRequest()->getGET($this->getModel()->getEntityPrimaryKey());
        if ($id) {
            $this->getModel()->load($id);
        }
    }

    /**
     * Return collection of all existing subject
     *
     * @return \Entities\Subject\Collection
     */
    public function getSubjectList()
    {
        $subjects = $this->getModel()->getCollection();
        return $subjects;
    }
}