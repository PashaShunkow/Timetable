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
     * @return string || array
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

    public function load($id)
    {
        $pairs = array($this->getEntityConfig('entity_id') => $id);
        $dbAdapter = $this->getDbAdapter();
        /** @var \System\DbAdapter\Select $select */
        $select = $dbAdapter->defineCurrentAction('select');
        $select->setTable($this->getEntityConfig('table'));
        $select->addToSelect('*');
        $select->where($pairs, DbAdapter::SQL_CONDITION_EQ);
        $select->prepareQuery();
        if ($select->execute()) {
            $result = $select->fetch(\PDO::FETCH_ASSOC);
            $this->_fillModelData($result);
            return $this;
        }else{
            App::error('Cant load model by id: ' . $id);
        }
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
            /** @var \System\DbAdapter\Insert $insert */
            $insert = $dbAdapter->defineCurrentAction('insert');
            $insert->setTable($this->getEntityConfig('table'));
            $insert->setBindPairs($modelData);
            $insert->prepareQuery();
            if (!$insert->execute()) {
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
        /** @var \System\DbAdapter\Describe $describe */
        $describe = $dbAdapter->defineCurrentAction('describe');
        $describe->setTable($this->getEntityConfig('table'));
        $describe->prepareQuery();
        if ($describe->execute()) {
            $output = $describe->fetchAll();
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

    /**
     * Fill inner data arrays
     *
     * @param array $data Data array
     *
     * @return $this
     */
    protected function _fillModelData($data)
    {
        $this->setData($data);
        $this->setOrigData(null, null);
        return $this;
    }
}