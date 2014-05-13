<?php
/**
 * System router service
 *
 * @category  TimetableTool
 * @package   TimetableTool_System
 * @author    Paul Shunkow
 * @copyright 2014 Paul Shunkow
 */
namespace System;

use \App;

class Router
{
    /**
     * Default route data
     */
    protected $_entity = 'menu';
    protected $_view   = 'view';
    protected $_action = 'index';

    /**
     * Route on related view
     */
    public function route()
    {
        $requestUri = $_SERVER['REQUEST_URI'];
        if(strpos($requestUri, '/') !== false)
        {
            if (strpos($requestUri, '/') === 0) {
                $requestUri = substr($requestUri, 1);
            }
            $parts = explode('/', $requestUri);
            if (!empty($parts[0])) {
                $this->_entity = $parts[0];
            }
            if (!empty($parts[1])) {
                $this->_view = $parts[1];
            }
            if (!empty($parts[2])) {
                $this->_action = $parts[2];
            }
        }
        $layout = App::instance()->getService('layout');
        $layout->setRootView($layout->initRootView());
        $layout->getRootView()->setInnerView('content', $layout->initView($this->_getActionData()));
    }

    /**
     * Return data for current action
     *
     * @return array
     */
    protected function _getActionData()
    {
        $actionData = array(
            'entity' => $this->_entity,
            'view'   => $this->_view,
            'action' => $this->_action
        );

        return $actionData;
    }
}

