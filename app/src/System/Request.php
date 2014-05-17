<?php

namespace System;

class Request
{
    protected $_getParams    = array();
    protected $_postParams   = array();
    protected $_serverParams = array();

    public function __construct()
    {
        $this->_loadGlobalArrays();
    }


    public function getGET($key = null)
    {
        if ($key == null) {
            return $this->_getParams;
        }
        if (isset($this->_getParams[$key])) {
            return $this->_getParams[$key];
        }
        return null;
    }

    public function setGET($key, $value = null)
    {
        if (is_array($key)) {
            $this->_getParams = array_merge($this->_getParams, $key);
        } elseif ($value !== null) {
            $this->_getParams[$key] = $value;
        }

        return $this;
    }

    public function getPOST($key = null)
    {
        if ($key == null) {
            return $this->_postParams;
        }
        if (isset($this->_postParams[$key])) {
            return $this->_postParams[$key];
        }
        return null;
    }

    public function setPOST($key, $value = null)
    {
        if (is_array($key)) {
            $this->_postParams = array_merge($this->_postParams, $key);
        } elseif ($value !== null) {
            $this->_postParams[$key] = $value;
        }

        return $this;
    }

    public function getSERVER($key = null)
    {
        if ($key == null) {
            return $this->_serverParams;
        }
        if (isset($this->_serverParams[$key])) {
            return $this->_serverParams[$key];
        }
        return null;
    }

    protected function _loadGlobalArrays()
    {
        $this->_getParams    = $_GET;
        $this->_postParams   = $_POST;
        $this->_serverParams = $_SERVER;
    }
}