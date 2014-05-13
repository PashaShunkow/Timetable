<?php
/**
 * System DB Adapter service
 *
 * @category  TimetableTool
 * @package   TimetableTool_System
 * @author    Paul Shunkow
 * @copyright 2014 Paul Shunkow
 */
namespace System;

use Entities\Object;
use \App;
use \PDO;

class DbAdapter extends Object
{
    const QUERY_STRING = '';

    const SQL_CONDITION_EQ   = ' = ';
    const SQL_CONDITION_NEQ  = ' != ';
    const SQL_CONDITION_GT   = ' > ';
    const SQL_CONDITION_LT   = ' < ';

    const SQL_OPERATOR_WHERE = 'WHERE ';
    const SQL_OPERATOR_AND   = ' AND ';

    const DB_CONFIG_AREA     = 'data_base';

    static protected  $_connection;

    protected $_statement;
    protected $_actions = array(
        'insert'   => 'System\DbAdapter\Insert',
        'describe' => 'System\DbAdapter\Describe',
        'select'   => 'System\DbAdapter\Select'
    );

    public function __construct()
    {
        $this->setQueryString(static::QUERY_STRING);
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
        if (!self::$_connection) {
            $dbConfig = $this->getDbConfig();
            $_connection = new PDO(
                'mysql:host='. $dbConfig['host'] .';dbname='. $dbConfig['dbname'] ,
                $dbConfig['user'],
                $dbConfig['pass']);
            self::$_connection = $_connection;
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
        if (!self::$_connection) {
            $this->createConnection();
        }
        return self::$_connection;
    }

    /**
     * Set bind pairs array
     *
     * @param array $data Model data
     *
     * @return $this
     */
    public function setBindPairs(array $data)
    {
        $this->setData('bind_pairs', $data);
        return $this;
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
        if ($this->getBindPairs()) {
            $this->_bindParams();
        }
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
    public function defineCurrentAction($actionName)
    {
        if ($this->_actions[$actionName]) {
            $currentActionName = $this->_actions[$actionName];
            $currentAction = new $currentActionName();
            return $currentAction;
        }
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
     * FetchAll wrapper
     *
     * @return array
     */
    public function fetchAll()
    {
        return $this->getStatement()->fetchAll();
    }

    /**
     * fetch wrapper
     *
     * @param null $fetch_style
     *
     * @return mixed
     */
    public function fetch($fetch_style = null)
    {
        return $this->getStatement()->fetch($fetch_style);
    }

    /**
     * Bind params in query string
     */
    protected function _bindParams()
    {
        $data = $this->getBindPairs();
        foreach ($data as $key => &$value) {
            $key = ':' . $key;
            $this->getStatement()->bindParam($key, $value);
        }
    }

    /**
     * Set table name into query string
     *
     * @param string $tableName Table name
     *
     * @return $this
     */
    public function setTable($tableName)
    {
        if($this->getQueryString())
        {
            $queryString = $this->getQueryString();
            $queryString = str_replace('{{table}}', $tableName, $queryString);
            $this->setQueryString($queryString);
            return $this;
        }else{
            $this->_notDefinedAction();
        }
    }

    protected function _notDefinedAction()
    {
        App::error('SQL Action should be defined before');
    }
}