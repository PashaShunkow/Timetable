<?php
/**
 * System layout service
 *
 * @category  TimetableTool
 * @package   TimetableTool_System
 * @author    Paul Shunkow
 * @copyright 2014 Paul Shunkow
 */
namespace System;

use \App;

class Layout
{
    const ROOT_ENTITY = 'root';
    const ROOT_VIEW   = 'view';
    const ROOT_MODEL  = 'model';
    const ROOT_ACTION = 'index';

    protected $_rootView;

    /**
     * Create view and related model
     *
     * @param array $viewData
     *
     * @return mixed
     *
     */
    public function initView(array $viewData)
    {
        $entityPath = 'Entities\\' . ucfirst($viewData['entity']) . '\\';
        $viewName  = $entityPath . ucfirst($viewData['view']);
        $modelName = $entityPath . ucfirst(self::ROOT_MODEL);
        $model = null;
        if (class_exists($modelName)) {
            $model = new $modelName(App::instance()->getService('config'), App::instance()->getService('dbAdapter'));
        }
        $view = new $viewName($model);
        $actionName = $viewData['action'] . 'Action';
        $view->setTemplate($this->_constructTemplateName($viewData));
        $view->$actionName();
        return $view;
    }

    /**
     * Create root view and related model
     *
     * @return mixed
     *
     */
    public function initRootView()
    {
        $rootViewData = array(
            'entity' => self::ROOT_ENTITY,
            'view'   => self::ROOT_VIEW,
            'action' => self::ROOT_ACTION
        );

        return $this->initView($rootViewData);
    }

    /**
     * Construct template name according to current URI
     *
     * @param array $viewData
     *
     * @return string
     */
    protected function _constructTemplateName($viewData)
    {
        $templateName = $viewData['entity'] . DIRECTORY_SEPARATOR . $viewData['view'] . DIRECTORY_SEPARATOR . $viewData['action'] . '.phtml';
        return $templateName;
    }

    /**
     * @param  Entities\AbstractView $view View object
     * @return $this
     */
    public function setRootView($view)
    {
        $this->_rootView = $view;
        return $this;
    }

    /**
     * Return root view object
     *
     * @return mixed
     */
    public function getRootView()
    {
        return $this->_rootView;
    }


    /**
     * Render all views
     */
    public function renderLayout($output = null)
    {
        if (!$output) {
            $output = 'getHtml';
        }

        $outHtml = $this->_rootView->$output();
        echo $outHtml;
    }
}