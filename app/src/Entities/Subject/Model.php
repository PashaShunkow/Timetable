<?php

namespace Entities\Subject;

use Entities\Template\AbstractModel;

use \App;

class Model extends AbstractModel
{
    public function delete()
    {
        $id = $this->getEntityId();
        if(parent::delete()){

            $teachers = App::createModel('teacher')->getCollection()
                ->addFieldToSelect(array('subjects'))
                ->load(false);

            foreach ($teachers as $teacher) {
                if ($subjects = $teacher->getData('subjects')) {
                    if (!is_array($subjects)) {
                        $subjects = array($subjects);
                    }
                    foreach ($subjects as $key => $subjectId) {
                        if ($id == $subjectId) {
                            unset($subjects[$key]);
                        }
                    }
                    $teacher->setData('subjects', $subjects)->save();
                }
            }

            return true;
        }
    }
}