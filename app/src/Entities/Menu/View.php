<?php

namespace Entities\Menu;

use Entities\Template\AbstractView;

class View extends AbstractView
{
    public function indexAction()
    {
        $this->setMenuText('i am menu text!!!!');
    }
}