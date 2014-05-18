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

    protected $_rootView;

    /**
     * Route on related view
     */
    public function route()
    {
        /** @var  $request  Request */
        $request = App::instance()->getService('request');
        $requestUri = $request->getSERVER('REQUEST_URI');
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
            if ($params = array_diff($parts, $this->_getActionData())) {
                foreach ($params as $param) {
                    if (strpos($param, '-') != false) {
                        $param = explode('-', $param, 2);
                        if (isset($param[0]) && isset($param[1]))
                        {
                            $request->setGET($param[0], $param[1]);
                        }
                    }
                }
            }
        }

        $factory = App::instance()->getService('factory');
        /** @var $rootView \Entities\Root\View */
        $rootView = $factory->initRootView();
        $rootView->setInnerView('content', $factory->initView($this->_getActionData()));
        App::instance()->getService('response')->setRootView($rootView);
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

