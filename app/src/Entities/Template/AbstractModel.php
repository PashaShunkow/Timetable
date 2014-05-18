<?php
/**
 * Abstract model class
 *
 * @category  TimetableTool
 * @package   TimetableTool_Entities\Template
 * @author    Paul Shunkow
 * @copyright 2014 Paul Shunkow
 */
namespace Entities\Template;

use System\Config;
use System\DbAdapter;
use \App;

abstract class AbstractModel extends AbstractEntityItem
{
    const ENTITY_TYPE = 'model';
    const ARRAY_FLAG  = '[]:';
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
     * Return items collection
     *
     * @return AbstractCollection
     */
    public function getCollection()
    {
        $entityNamespace = $this->_getEntityNamespace();
        $collectionName  = $entityNamespace . ucfirst(AbstractCollection::ENTITY_TYPE);
        return new $collectionName($this);
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
     * Return value if entity id fro current model
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData($this->getEntityConfig('entity_id'));
    }

    /**
     * Return name of entity table
     *
     * @return string
     */
    public function getEntityTable()
    {
        return $this->getEntityConfig('table');
    }

    /**
     * Return entity name of current model
     *
     * @return string
     */
    public function getEntityPrimaryKey()
    {
        return $this->getEntityConfig('entity_id');
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
        try {
            $pairs = array($this->getEntityPrimaryKey() => $id);
            $dbAdapter = $this->getDbAdapter();
            /** @var \System\DbAdapter\Select $select */
            $select = $dbAdapter->defineCurrentAction('select');
            $select->setTable($this->getEntityTable());
            $select->addToSelect(DbAdapter::SQL_INCLUDE_ALL);
            $select->where($pairs, DbAdapter::SQL_CONDITION_EQ);
            $select->prepareQuery();
            if ($select->execute()) {
                if ($result = $select->fetch(\PDO::FETCH_ASSOC)) {
                    $this->fillModelData($result);
                }
                return $this;
            } else {
                App::error('Cant load model by id: ' . $id);
            }
        } catch (\PDOException $e) {
            App::error('Cant load model: ' . $e->getMessage());
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
            if ($id = $this->getEntityId()) {
                /** @var \System\DbAdapter\Update $action */
                $action = $dbAdapter->defineCurrentAction('update');
            } else {
                /** @var \System\DbAdapter\Insert $action */
                $action = $dbAdapter->defineCurrentAction('insert');
            }
            $action->setTable($this->getEntityTable());
            $action->setBindPairs($modelData);
            if ($id) {
                $pairs = array($this->getEntityPrimaryKey() => $id);
                $action->where($pairs, DbAdapter::SQL_CONDITION_EQ);
            }
            $action->prepareQuery();
            $action->beginTransaction();
            $action->execute();
            $lastInsertId = $action->lastInsertId($this->getEntityTable());
            $action->commit();
            if ($lastInsertId != 0) {
                $this->setData($this->getEntityPrimaryKey(), $lastInsertId);
            }
        } catch (\PDOException $e) {
            $action->rollBack();
            App::error('Cant save model: ' . $e->getMessage());
        }
        return $this;
    }

    /**
     * Common delete model method
     *
     * @return bool
     */
    public function delete()
    {
        if (!$id = $this->getEntityId()) {
            return false;
        }
        $pairs = array($this->getEntityPrimaryKey() => $id);
        $dbAdapter = $this->getDbAdapter();
        /** @var \System\DbAdapter\Delete $delete */
        $delete = $dbAdapter->defineCurrentAction('delete');

        try {
            $result = $delete->setTable($this->getEntityTable())
                ->where($pairs, DbAdapter::SQL_CONDITION_EQ)
                ->prepareQuery()
                ->execute();
            if ($result) {
                $this->flushModelData();
            }
            return $result;
        } catch (\PDOException $e) {
            App::error('Cant delete model: ' . $e->getMessage());
        }
    }

    /**
     * Return entity namespace
     *
     * @return string
     */
    protected function _getEntityNamespace()
    {
        $namespace = get_class($this);
        $namespace = str_replace(ucfirst(self::ENTITY_TYPE), '', $namespace);
        return $namespace;
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
        $describe->setTable($this->getEntityTable());
        $describe->prepareQuery();
        if ($describe->execute()) {
            $output = $describe->fetchAll();
            $tableMap = array();
            foreach ($output as $column) {
                $tableMap[$column['Field']] = true;
            }
            /**
             * Remove not existing in DB keys
             */
            $data = array_intersect_key($data, $tableMap);
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = self::ARRAY_FLAG . implode(',', $value);
                }
            }
            return $data;
        } else {
            App::error('Cant map a table (name): ' . $this->getEntityConfig('table') . ' probably it is not exist', true);
        }
    }

    /**
     * Fill inner data arrays
     *
     * @param array $data Data array
     *
     * @return $this
     */
    public function fillModelData($data)
    {
        foreach ($data as $key => $value) {
            if (strpos($value, self::ARRAY_FLAG) !== false) {
                $value = str_replace(self::ARRAY_FLAG, '', $value);
                if (strpos($value, ',') !== false) {
                    $value = explode(',', $value);
                }
                $data[$key] = $value;
            }
        }
        $this->setData($data);
        $this->setOrigData(null, null);
        return $this;
    }

    /**
     * Flush inner data arrays
     *
     * @return $this
     */
    public function flushModelData()
    {
        $this->unsetData();
        $this->setOrigData(null, null);
        return $this;
    }
}