<?php

namespace System;

use Entities\Object;
use \App;
use \PDO;

class DbAdapter extends Object
{
    const KEY_QUERY_ENTITY   = 'keys';
    const VALUE_QUERY_ENTITY = 'values';

    const DB_CONFIG_AREA     = 'data_base';

    const ACTION_METHOD_ROOT = '_prepareStringFor';

    protected $_modelData;
    protected $_statement;
    protected $_actions = array(
        'insert'   => 'INSERT INTO {{table}} {{keys}} VALUES {{values}}',
        'describe' => 'DESCRIBE {{table}}'
    );

    public function __construct()
    {
        $this->setData('db_config', App::instance()->getService('config')->getConfigArea(self::DB_CONFIG_AREA));
        $this->createConnection();
    }

    /**
     * Create connection to db
     *
     * @return $this
     */
    public function createConnection()
    {
        if (!$this->getData('connection')) {
            $dbConfig = $this->getDbConfig();
            $_connection = new PDO(
                'mysql:host='. $dbConfig['host'] .';dbname='. $dbConfig['dbname'] ,
                $dbConfig['user'],
                $dbConfig['pass']);
            $this->setConnection($_connection);
        }
        return $this;
    }

    /**
     * Return db connection
     *
     * @return mixed
     */
    public function getConnection()
    {
        if (!$this->getData('connection')) {
            $this->createConnection();
        }
        return $this->getData('connection');
    }

    /**
     * Set model data
     *
     * @param array $data Model data
     *
     * @return $this
     */
    public function setModelData($data)
    {
        $this->_modelData = $data;
        return $this;
    }

    public function getModelData()
    {
        if (empty($this->_modelData) || !is_array($this->_modelData)) {
            App::error('Model data empty or wrong formated (should be an array)');
        }
        return $this->_modelData;
    }

    /**
     * Set PDO statement object into inner variable
     *
     * @param \PDOStatement $statement
     *
     * @return $this
     */
    public function setStatement(\PDOStatement $statement)
    {
        $this->_statement = $statement;
        return $this;
    }

    /**
     * Return PDOStatement
     *
     * @return \PDOStatement
     */
    public function getStatement()
    {
        return $this->_statement;
    }

    /**
     * Set current SQL action and name
     *
     * @param string $actionName Action name
     *
     * @return $this
     */
    public function setCurrentAction($actionName)
    {
        $action = $this->getAction($actionName);
        $this->setData('current_action', array($actionName => $action));
        return $this;
    }

    /**
     * Return current SQL action
     *
     * @return string
     */
    public function getCurrentAction()
    {
        $action = $this->getData('current_action');
        return reset($action);
    }


    /**
     * Return current SQL action name
     *
     * @return string
     */
    public function getCurrentActionName()
    {
        $actionName = array_keys($this->getData('current_action'));
        return reset($actionName);
    }

    /**
     * Return action string
     *
     * @param string $key Action key
     *
     * @return mixed
     */
    public function getAction($key)
    {
        if (!isset($this->_actions[$key])) {
            App::error('There is no such SQL action: ' . $key);
        }
        return $this->_actions[$key];
    }

    /**
     * Execute SQL query
     *
     * @return bool
     */
    public function execute()
    {
       return $this->getStatement()->execute();
    }

    /**
     * Fetch sql query result
     *
     * @return array
     */
    public function fetchAll()
    {
        return $this->getStatement()->fetchAll();
    }

    /**
     * Bind params in query string
     */
    public function bindParams()
    {
        $data = $this->getModelData();
        foreach ($data as $key => &$value) {
            $key = ':' . $key;
            $this->getStatement()->bindParam($key, $value);
        }
    }

    /**
     * Construct and return query string
     *
     * @return string
     */
    public function prepareQueryString()
    {
        $actionName  = $this->getCurrentActionName();
        $actionMethodName = self::ACTION_METHOD_ROOT . ucfirst($actionName);

        if (!is_callable(array($this, $actionMethodName))) {
            App::error('Cant call action method: ' . $actionMethodName . ' should be defined in: ' . get_class($this));
        }

        $queryString = $this->$actionMethodName($this->getCurrentAction());

        return $queryString;
    }

    /**
     * Prepare query string fro insert SQL action
     *
     * @param string $queryString Query string for insert action
     *
     * @return string
     */
    protected function _prepareStringForInsert($queryString)
    {
        $keys   = $this->_preparePlaceholdersInQueryString(self::KEY_QUERY_ENTITY);
        $values = $this->_preparePlaceholdersInQueryString(self::VALUE_QUERY_ENTITY);

        $queryString = str_replace(
            array('{{table}}', '{{keys}}', '{{values}}'),
            array($this->getTable(), $keys, $values),
            $queryString
        );

        return $queryString;
    }

    /**
     * Prepare query string fro describe SQL action
     *
     * @param string $queryString Query string for insert action
     *
     * @return string
     */
    protected function _prepareStringForDescribe($queryString)
    {
        $queryString = str_replace(
            array('{{table}}'),
            array($this->getTable()),
            $queryString
        );

        return $queryString;
    }

    /**
     * Convert data into query entities (keys, values)
     *
     * @param string $type entity in query string
     *
     * @return string
     */
    protected function _preparePlaceholdersInQueryString($type)
    {
        $_prefix = '';

        switch ($type) {
            case self::KEY_QUERY_ENTITY :
                $_prefix = '';
                break;
            case self::VALUE_QUERY_ENTITY :
                $_prefix = ':';
                break;
        }

        $data = $this->getModelData();

        $preparedEntities = '(';
        $i = 0;
        foreach ($data as $key => $values) {
            if ($i != 0) {
                $preparedEntities .= ', ';
            }
            $preparedEntities .= $_prefix . $key;
            $i++;
        }
        $preparedEntities .= ')';
        return $preparedEntities;
    }
}