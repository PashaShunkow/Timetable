<?php

namespace System;

class Request
{
    protected $_getParams  = array();
    protected $_postParams = array();

    public function getGET($key = null)
    {
        if ($key == null) {
            return $this->_getParams;
        }
        if (isset($this->_getParams[$key])) {
            return $this->_getParams[$key];
        }
    }
}