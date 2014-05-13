<?php

namespace Entities\Root;

use Entities\Template\AbstractView;
use \App;

class View extends AbstractView
{
    protected $_views = array(
        'content' => array(),
        'head'    => array(),
    );

    public function indexAction()
    {
        $this->setTest('root text!!');
    }

    /**
     * Return html of all inner views in content area
     *
     * @return string
     */
    public function getContent()
    {
        $html = '';
        foreach ($this->getInnerViews('content') as $view) {
            $html .= $view->getHtml();
        }
        return $html;
    }

    /**
     * Return all inner views for current view
     *
     * @param string $area key in views array
     *
     * @return array
     */
    public function getInnerViews($area)
    {
        if (isset($this->_views[$area]) && is_array($this->_views[$area])) {
            return $this->_views[$area];
        }
        App::error('Not such area: "' . $area . '" in child view for current view: ' . get_class($this));
    }

    /**
     * Set view item into some view area
     *
     * @param string $area     View area
     * @param object $view     View object
     * @return $this|void
     */
    public function setInnerView($area, $view)
    {
        if (!isset($this->_views[$area])) {
            App::error('Passed area: "' . $area . '" is not exist.');
        }
        $this->_views[$area][] = $view;
        return $this;
    }
}