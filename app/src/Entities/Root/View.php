<?php

namespace Entities\Root;

use Entities\Template\AbstractView;
use \App;

class View extends AbstractView
{
    public function indexAction()
    {

    }

    /**
     * Return html of all inner views in content area
     *
     * @return string
     */
    public function getContent()
    {
        $html = $this->getInnerView('content')->getHtml();
        return $html;
    }

    public function getTitle()
    {
        return $this->getInnerView('content')->getTitle();
    }
}