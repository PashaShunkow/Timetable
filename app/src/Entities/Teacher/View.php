<?php

namespace Entities\Teacher;

use Entities\Template\AbstractView;

class View extends AbstractView
{
    public function indexAction()
    {
       $this->getModel()->setData(
           array(
               'name'     => '888AAAAAAA',
               'lastname' => '45BBBBBBB',
               'sddfsdfsdfsdf' => 'dfdfdf'
           )
       )->save();
    }
}