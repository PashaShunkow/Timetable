<?php

namespace System;

use \App;

class Response {

    protected $_output = '';
    protected $_rootView;

    /**
     * @param  \Entities\Root\View $view View object
     * @return $this
     */
    public function setRootView(\Entities\Root\View $view)
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
    public function startRender($output = null)
    {
        if (!$output) {
            $output = 'getHtml';
        }

        $this->_output = $this->_rootView->$output();
        return $this;
    }

    /**
     * Render html output
     */
    public function getHtml()
    {
        echo $this->_output;
    }

    /**
     * Set or add html output
     *
     * @param string  $html          Html output
     * @param bool    $replaceOutput If true current output will be replaced
     * @return $this
     */
    public function setOutput($html, $replaceOutput = true)
    {
        if ($replaceOutput) {
            $this->_output = '';
        }
        $this->_output = $html;
        return $this;
    }

    /**
     * Redirect on specified url
     *
     * @param string $url                  Base url
     * @param bool   $removeExistingParams Set true fro get clear URL (without old get params)
     * @param null   $params Params array
     */
    public function redirectOn($url, $params = null, $removeExistingParams = false)
    {
        if ($removeExistingParams || $params) {
            $url = $this->_rebuildUrl($url);
        }
        if ($params) {
            $url .= App::convertParamsToString($params);
        }
        header('Location: ' . $url);
    }

    /**
     * Rebuild url for remove 'old' get params
     *
     * @param  string $url Url
     * @return string
     */
    protected function _rebuildUrl($url)
    {
        $requestUri = str_replace(App::getBaseUrl(), '', $url);
        if($requestUri && strpos($requestUri, '/') !== false){
            if (strpos($requestUri, '/') === 0) {
                $requestUri = substr($requestUri, 1);
            }
            $parts = explode('/', $requestUri);
            $requestUri = '';
            for ($i = 0; $i < 3; $i++) {
                if (isset($parts[$i])) {
                    $requestUri .= $parts[$i] . '/';
                }
            }
        }
        return App::getBaseUrl() . rtrim($requestUri, '/');
    }
}