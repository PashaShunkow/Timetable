<?php
/**
 * DbAdapter extension for INSERT query type
 *
 * @category  TimetableTool
 * @package   TimetableTool_System\DbAdapter
 * @author    Paul Shunkow
 * @copyright 2014 Paul Shunkow
 */
namespace System\DbAdapter;

use System\DbAdapter;

class Insert extends DbAdapter
{
    const QUERY_STRING = 'INSERT INTO {{table}} {{keys}} VALUES {{values}}';

    const KEY_QUERY_ENTITY   = 'keys';
    const VALUE_QUERY_ENTITY = 'values';

    public function __construct()
    {
        $this->setQueryString(static::QUERY_STRING);
    }

    /**
     * Prepare query string before executing
     *
     * @return $this
     */
    public function prepareQuery()
    {
        $queryString = $this->_prepareStringForInsert($this->getQueryString());
        $this->setStatement($this->getConnection()->prepare($queryString));
        return $this;
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
            array('{{keys}}', '{{values}}'),
            array($keys, $values),
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

        $data = $this->getBindPairs();

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