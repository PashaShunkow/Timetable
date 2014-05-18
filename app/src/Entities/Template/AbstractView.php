<?php

namespace Entities\Template;

use \App;

abstract class AbstractView extends AbstractEntityItem
{
    protected $_model;
    protected $_formBuilder;

    protected $_template = '';

    protected $_views = array();

    public function __construct(AbstractModel $model = null, ViewElements\FormBuilder $formBuilder = null)
    {
        $this->_model       = $model;
        $this->_formBuilder = $formBuilder->bindView($this);
    }

    /**
     * Return html code of current view
     *
     * @return string
     */
    public function getHtml()
    {
        $templateFile = $this->getTemplateFile();
        if ($templateFile == '' || !is_file($templateFile)) {
            App::error('Cant find template file (or empty): ' . $templateFile);
        }
        ob_start();
        require_once $templateFile;
        $html = ob_get_clean();
        return $html;
    }

    /**
     * Return request object
     *
     * @return \System\Request
     */
    public function getRequest()
    {
        return App::instance()->getService('request');
    }

    /**
     * Return response object
     *
     * @return \System\Response
     */
    public function getResponse()
    {
        return App::instance()->getService('response');
    }

    /**
     * Return inner model
     *
     * @return AbstractModel
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * Set template for current view
     *
     * @param $name
     * @return $this
     */
    public function setTemplate($name)
    {
        $this->_template = $name;
        return $this;
    }

    /**
     * Return path to phtml template file
     *
     * @return string
     */
    public function getTemplateFile()
    {
        $templateFile = App::getTemplateDir() . DIRECTORY_SEPARATOR . $this->_template;
        return $templateFile;
    }

    /**
     * Set inner view into current view
     *
     * @param $viewName
     * @param $view
     * @return $this
     */
    public function setInnerView($viewName, $view)
    {
        $this->_views[$viewName] = $view;
        return $this;
    }

    /**
     * Return inner view by name
     *
     * @param string $viewName key in views array
     * @return mixed
     */
    public function getInnerView($viewName)
    {
        if (isset($this->_views[$viewName]) && !is_array($this->_views[$viewName])) {
            return $this->_views[$viewName];
        }
        App::error('Not such inner view: "' . $viewName . '" in child view in current view: "' . get_class($this) . '"');
    }

    /**
     * Return base url and route if specified
     *
     * @param null $route Route
     * @param  array  $params Get params
     *
     * @return string
     */
    public function getBaseUrl($route = null, $params = array())
    {
        return App::getBaseUrl($route, $params);
    }

    /**
     * Translate function
     *
     * @param string $string Incoming string
     *
     * @return string
     */
    public function tr($string)
    {
        return $string;
    }

    /**
     * Return include html of .js file in skin folder
     *
     * @param  string $filename
     * @return string
     */
    public function getSkinJs($filename)
    {
        $html = '';
        if ($fileUrl = $this->_getSkinFileUrl($filename)) {
            $html = '<script type="text/javascript" src="' . $fileUrl . '"></script>';
        }
        return $html;
    }

    /**
     * Return include html of .css file in skin folder
     *
     * @param  string $filename
     * @return string
     */
    public function getSkinCss($filename)
    {
        $html = '';
        if ($fileUrl = $this->_getSkinFileUrl($filename)) {
            $html = '<link rel="stylesheet" type="text/css" href="' . $fileUrl . '" media="all" />';
        }
        return $html;
    }

    /**
     * Return entity name
     *
     * @return null|string
     */
    public function getEntityName()
    {
        if($model = $this->getModel())
        {
            return $model->getEntityName();
        }
        return null;
    }

    /**
     * Return form builder object
     *
     * @return ViewElements\FormBuilder
     */
    public function formBuilder()
    {
        return $this->_formBuilder;
    }

    /**
     * Return URL of file in skin folder
     *
     * @param $filename
     * @return null|string
     */
    protected function _getSkinFileUrl($filename)
    {
        $file = App::getSkinDir() . DIRECTORY_SEPARATOR . $filename;
        $fileUrl = $this->getBaseUrl() . App::APP_FOLDER . '/' . App::RESOURCE_FOLDER . '/' . App::SKIN_FOLDER . '/' . $filename;
        if (is_file($file)) {
            return $fileUrl;
        }
        return null;
    }
}