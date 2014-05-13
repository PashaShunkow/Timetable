<?php

namespace Entities\Template;

use \App;

abstract class AbstractView extends AbstractEntityItem
{
    protected $_model;

    protected $_template = '';

    protected $_views = array();

    public function __construct(AbstractModel $model = null)
    {
        if ($model) {
            $this->_model = $model;
        }
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
     *
     * @return string
     */
    public function getBaseUrl($route = null)
    {
        return App::getBaseUrl($route);
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
}