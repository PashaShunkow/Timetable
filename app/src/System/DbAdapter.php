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

    const SQL_OPERATOR_WHERE  = 'WHERE ';
    const SQL_OPERATOR_AND    = ' AND ';
    const SQL_OPERATOR_OR     = ' OR ';
    const SQL_OPERATOR_IN     = ' IN ';
    const SQL_OPERATOR_NOT_IN = ' NOT IN';

    const SQL_INCLUDE_ALL    = '*';

    const DB_CONFIG_AREA     = 'data_base';

    protected $_statement;
    static protected  $_connection;

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
     * @return \PDO
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
     * Return object of required SQL action
     *
     * @param string $actionName Action name
     *
     * @return $this
     */
    public function defineCurrentAction($actionName)
    {
        $currentActionName = 'System\DbAdapter\\' . ucfirst($actionName);
        if (class_exists($currentActionName)) {
            $currentAction = new $currentActionName();
            return $currentAction;
        }else{
            App::error('There is no such SQL action: ' . $actionName);
        }
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
     * beginTransaction method wrapper
     */
    public function beginTransaction()
    {
        $this->getConnection()->beginTransaction();
    }

    /**
     * commit method wrapper
     */
    public function commit()
    {
        $this->getConnection()->commit();
    }

    /**
     * rollBack method wrapper
     */
    public function rollBack()
    {
        $this->getConnection()->rollBack();
    }

    /**
     * lastInsertId method wrapper
     *
     * @param string $name
     *
     * @return string
     */
    public function lastInsertId($name = null)
    {
        return $this->getConnection()->lastInsertId($name);
    }

    /**
     * FetchAll wrapper
     *
     * @param null $fetch_style
     *
     * @return array
     */
    public function fetchAll($fetch_style = null)
    {
        return $this->getStatement()->fetchAll($fetch_style);
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
            App::error('SQL Action should be defined before');
        }
    }

    /**
     * Add where sql operator to query string
     *
     * @param array  $pairs key => value pairs
     * @param string $condition eq, neq and etc.
     *
     * @return $this
     */
    public function where($pairs, $condition)
    {
        $queryString = $this->getQueryString();
        foreach ($pairs as $key => $value) {
            if ($this->_isWhereExist($queryString)) {
                $queryString .= self::SQL_OPERATOR_AND;
            } else {
                $queryString .= self::SQL_OPERATOR_WHERE;
            }
            if (is_array($value)) {
                if ($condition == self::SQL_CONDITION_EQ) {
                    $subCondition = self::SQL_OPERATOR_IN;
                } elseif ($condition == self::SQL_CONDITION_NEQ) {
                    $subCondition = self::SQL_OPERATOR_NOT_IN;
                } else {
                    App::error('With operator: ' . $condition . ' one dimension $pairs array expected', true);
                }
                $queryString .= $key . $subCondition . '(';
                foreach ($value as $one) {
                    $queryString .= "'{$one}', ";
                }
                $queryString = rtrim($queryString, ', ') . ')';
                /**
                 * If value passed not as placeholder it should be excluded from bind pairs
                 */
                unset($pairs[$key]);
            } else {
                $queryString .= $key . $condition . ':' . $key;
            }

        }
        if ($this->getBindPairs() && !empty($pairs)) {
            $pairs = array_merge($this->getBindPairs(), $pairs);
        }
        $this->setBindPairs($pairs);
        $this->setQueryString($queryString);
        return $this;
    }

    /**
     * Check if WHERE was applied to current select
     *
     * @param string $queryString
     *
     * @return bool
     */
    protected function _isWhereExist($queryString)
    {
        if (strpos($queryString, self::SQL_OPERATOR_WHERE) !== false) {
            return true;
        }
        return false;
    }
}