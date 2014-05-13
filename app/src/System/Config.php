<?php

namespace System;

use Entities\Template\AbstractEntityItem;

use \App;

class Config
{
    const CONFIG_FILE    = 'config.json';

    protected $_entityConfigs = array();
    protected $_systemConfigs = array();

    public function __construct()
    {
        $this->_init();
    }

    /**
     * Return config part for current entity
     *
     * @param $entityObject
     *
     * @return array
     */
    public function getEntityConfig(AbstractEntityItem $entityObject)
    {
        $entityKey = $entityObject->getEntityKey();
        return $this->_getEntityConfigs($entityKey);
    }

    /**
     * Return part of system config array or whole array
     *
     * @param null||string $path Path in system config array
     *
     * @return array
     */
    public function getConfigArea($path = null)
    {
        $configArea = $this->_systemConfigs;
        if (!$path) {
            return $configArea;
        } elseif (strpos($path, '/') !== false) {
            $path = explode('/', $path);
            if (is_array($path)) {
                foreach ($path as $key) {
                    if (isset($configArea[$key])) {
                        $configArea = $configArea[$key];
                    }
                }
            }
            return $configArea;
        } elseif (isset($configArea[$path])) {
            return $configArea[$path];
        }

        return null;
    }

    /**
     * Init the config object
     */
    protected function _init()
    {
        $this->_parseSystemConfig();
        $entityDir = App::getEntityDir();
        if ($handle = opendir($entityDir)) {

            while (false !== ($entry = readdir($handle))) {
                if (is_dir($entityDir . DIRECTORY_SEPARATOR . $entry) && ctype_alpha($entry[0])) {
                    $this->_entityConfigs[lcfirst($entry)] = $entityDir . DIRECTORY_SEPARATOR . $entry;
                }
            }

            closedir($handle);
            $this->_parseEntityConfig();
        }
    }

    /**
     * Try to parse config in each entity folder
     *
     * @return void;
     */
    protected function _parseEntityConfig()
    {
        foreach($this->_getEntityConfigs() as $entityKey => $entityDir)
        {
            if($handle = opendir($entityDir))
            {
                while (false !== ($entry = readdir($handle))) {
                    $content = null;
                    if ($entry == self::CONFIG_FILE) {
                        $configFile = $entityDir . DIRECTORY_SEPARATOR . self::CONFIG_FILE;
                        $content = file_get_contents($configFile);
                        if (!is_array($this->_entityConfigs[$entityKey])) {
                            $this->_entityConfigs[$entityKey] = App::JSONDecode($content, true);
                        }
                    }
                }
            }
            closedir($handle);
            if (!is_array($this->_entityConfigs[$entityKey])) {
                $this->_entityConfigs[$entityKey] = null;
            }
        }
    }

    /**
     * Return all configs or part by key
     *
     * @param string $key
     *
     * @return array|null
     */
    protected function _getEntityConfigs($key = null)
    {
        if (!$key) {
            return $this->_entityConfigs;
        } elseif (isset($this->_entityConfigs[$key])) {
            return $this->_entityConfigs[$key];
        }
        return null;
    }

    /**
     * Parse system config
     *
     * @return array
     */
    protected function _parseSystemConfig()
    {
        $_sysConfigFile = App::getSrcDir() . DIRECTORY_SEPARATOR . self::CONFIG_FILE;
        if (!is_file($_sysConfigFile)) {
            App::error('Cant find system config file! Should be in : ' . $_sysConfigFile);
        }
        $_sysConfig = App::JSONDecode(file_get_contents($_sysConfigFile), true);
        if (empty($_sysConfig)) {
            App::error('System config is empty or unreadable');
        }
        return $this->_systemConfigs = $_sysConfig;
    }
}