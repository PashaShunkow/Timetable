<?php

namespace Entities\Teacher;

use Entities\Template\AbstractModel;
use Entities\Template\AbstractView;

use \App;
use System\DbAdapter;

class View extends AbstractView
{
    protected $_subjectsLabels = array();

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

    public function getSubjectsLabels(AbstractModel $teacher)
    {
        $subjectTpl = App::getTemplateOf('subject');
        $subjects = $this->formBuilder()->prepareOptions('subject_ids', $teacher, $subjectTpl);
        $labels = array();
        foreach ($subjects as $subject) {
            if ($subject['selected']) {
                $labels[] = $subject['label'];
            }
        }
        return $labels;
    }
}