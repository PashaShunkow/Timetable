<?php
/**
 * System factory service
 *
 * @category  TimetableTool
 * @package   TimetableTool_System
 * @author    Paul Shunkow
 * @copyright 2014 Paul Shunkow
 */
namespace System;

use \App;

class Factory
{
    const ROOT_ENTITY = 'root';
    const ROOT_VIEW   = 'view';
    const ROOT_MODEL  = 'model';
    const ROOT_ACTION = 'index';

    const ENTITY_NAMESPACE = 'Entities';
    const NS_SEPARATOR     = '\\';

    protected $_templates = array();

    /**
     * Create view and related model
     *
     * @param array $viewData
     *
     * @return mixed
     */
    public function initView(array $viewData)
    {
        $view = $this->createView(ucfirst($viewData['entity']), ucfirst($viewData['view']), true, true);
        $actionName = $viewData['action'] . 'Action';
        $view->setTemplate($this->_constructTemplateName($viewData));
        $view->$actionName();
        return $view;
    }

    /**
     * Return view object
     *
     * @param string        $entityName
     * @param string        $viewName
     * @param array | bool  $modelData Specify this parameter as true if you need default inner model or as array if you need not default model
     * @param bool          $initFormBuilder
     * @return \Entities\Template\AbstractView
     */
    public function createView($entityName, $viewName = self::ROOT_VIEW, $modelData = false, $initFormBuilder = false)
    {
        $viewClassName = self::ENTITY_NAMESPACE . self::NS_SEPARATOR . $entityName . self::NS_SEPARATOR . $viewName;
        $model       = null;
        $formBuilder = null;

        if($modelData)
        {
            $modelEntityName = $entityName;
            $modelName       = self::ROOT_MODEL;
            if ($modelData && is_array($modelData) && isset($modelData['entity_namespace']) && isset($modelData['model_name'])) {
                $modelEntityName = $modelData['entity_namespace'];
                $modelName = $viewName['model_name'];
            }
            $model = $this->createModel($modelEntityName, $modelName);
        }
        if ($initFormBuilder) {
            $formBuilderClass = self::ENTITY_NAMESPACE . self::NS_SEPARATOR . 'Template' . self::NS_SEPARATOR . 'ViewElements' . self::NS_SEPARATOR . 'FormBuilder';
            if (class_exists($formBuilderClass)) {
                $formBuilder = new $formBuilderClass();
            }
        }
        if (class_exists($viewClassName)) {
            return new $viewClassName($model, $formBuilder);
        }
        App::error('Cant init view , class: ' . $viewClassName . ' is not exist');
        return null;
    }

    /**
     * Return model object
     *
     * @param string $entityName
     * @param string $modelName
     * @return \Entities\Template\AbstractModel
     */
    public function createModel($entityName, $modelName = self::ROOT_MODEL)
    {
        $modelClassName = self::ENTITY_NAMESPACE . self::NS_SEPARATOR . $entityName .self::NS_SEPARATOR . $modelName;
        if (class_exists($modelClassName)) {
            return new $modelClassName(App::instance()->getService('config'), App::instance()->getService('dbAdapter'));
        }
        return null;
    }

    /**
     * Return empty template of entity
     *
     * @param $entityName
     * @param string $modelName
     * @return mixed
     */
    public function getTemplateOf($entityName, $modelName = self::ROOT_MODEL)
    {
        if (!isset($this->_templates[$entityName][$modelName])) {
            $this->_templates[$entityName][$modelName] = $this->createModel($entityName, $modelName);
        }

        $template = $this->_templates[$entityName][$modelName];

        return $template->flushModelData();
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
}