<?php

namespace Entities\Template;

use System\Config;
use System\DbAdapter;
use \App;

abstract class AbstractModel extends AbstractEntityItem
{
    const ENTITY_TYPE = 'model';
    protected $_entityConfig = array();
    /**
     * @var \System\Config
     */
    protected $_config;

    /**
     * @var \System\DbAdapter
     */
    protected $_dbAdapter;

    public function __construct(Config $config, DbAdapter $dbAdapter)
    {
        $this->_config = $config;
        $this->_dbAdapter = $dbAdapter;
        $this->getEntityConfig();
    }

    /**
     * Return config for current entity
     *
     * @param string $key Key in config array
     *
     * @return array
     */
    public function getEntityConfig($key = null)
    {
        if (empty($this->_entityConfig)) {
            $this->_entityConfig = $this->_config->getEntityConfig($this);
            $this->_entityConfig = $this->_entityConfig[$this->getEntityType()];
        }
        if ($key && isset($this->_entityConfig[$key])) {
            return $this->_entityConfig[$key];
        }
        return $this->_entityConfig;
    }

    /**
     * Return db adapter
     *
     * @return DbAdapter
     */
    public function getDbAdapter()
    {
        return $this->_dbAdapter;
    }

    /**
     * Common save model method
     *
     * @return $this
     */
    public function save()
    {
        try {
            $modelData = $this->_prepareDataBeforeSave($this->getData());
            $dbAdapter = $this->getDbAdapter();
            $dbAdapter->setTable($this->getEntityConfig('table'));
            $dbAdapter->setCurrentAction('insert');
            $dbAdapter->setModelData($modelData);
            $dbAdapter->setQueryString($dbAdapter->prepareQueryString());
            $dbAdapter->setStatement($dbAdapter->getConnection()->prepare($dbAdapter->getQueryString()));
            $dbAdapter->bindParams();
            $result = $dbAdapter->execute();
            if (!$result) {
                App::error('Cant save the model!');
            }
        } catch (\PDOException $e) {
            App::error('Cant save model: ' . $e->getMessage());
        }
        return $this;
    }

    /**
     * Prepare model data to save into DB,
     * remove temporarily keys from data array
     *
     * @param array $data
     *
     * @return array
     */
    protected function _prepareDataBeforeSave(array $data)
    {
        $dbAdapter = $this->getDbAdapter();
        $dbAdapter->setTable($this->getEntityConfig('table'));
        $dbAdapter->setCurrentAction('describe');
        $dbAdapter->setQueryString($dbAdapter->prepareQueryString());
        $dbAdapter->setStatement($dbAdapter->getConnection()->prepare($dbAdapter->getQueryString()));
        $result = $dbAdapter->execute();
        if ($result) {
            $output = $dbAdapter->fetchAll();
            $tableMap = array();
            foreach ($output as $column) {
                $tableMap[$column['Field']] = true;
            }
            $data = array_intersect_key($data, $tableMap);
            return $data;
        } else {
            App::error('Cant map a table (name): ' . $this->getEntityConfig('table') . ' probably it is not exist');
        }
    }
}