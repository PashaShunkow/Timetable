<?php
/**
 * DbAdapter extension for SELECT query type
 *
 * @category  TimetableTool
 * @package   TimetableTool_System\DbAdapter
 * @author    Paul Shunkow
 * @copyright 2014 Paul Shunkow
 */
namespace System\DbAdapter;

use System\DbAdapter;

class Select extends  DbAdapter
{
    const QUERY_STRING = 'SELECT {{needle}} FROM {{table}} ';

    public function __construct()
    {
        $this->setQueryString(static::QUERY_STRING);
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
            }else{
                $queryString .= self::SQL_OPERATOR_WHERE;
            }
            $queryString .= $key . $condition . ':' . $key;
        }
        if ($bindPairs = $this->getBindPairs()) {
            $pairs = array_merge($bindPairs, $pairs);
        }
        $this->setBindPairs($pairs);
        $this->setQueryString($queryString);
        return $this;
    }

    /**
     * Add needle to select query
     *
     * @param string $needle Aim of select query
     *
     * @return $this
     */
    public function addToSelect($needle)
    {
        if (!is_array($needle)) {
            $needle = array($needle);
        }
        $currentNeedles = $this->getNeedles();
        if (!$currentNeedles) {
            $this->setNeedles($needle);
        } else {
            $this->setNeedles(array_merge($currentNeedles, $needle));
        }
        return $this;
    }

    /**
     * Prepare query string before executing
     *
     * @return $this
     */
    public function prepareQuery()
    {
        if ($needles = $this->getNeedles()) {
            $queryString = $this->getQueryString();
            $needlesString = '';
            for ($i = 0; $i < count($needles); $i++) {
                $needlesString .= $needles[$i];
                if ($i > 0) {
                    $needlesString .= ', ';
                }
            }
            $queryString = str_replace('{{needle}}', $needlesString, $queryString);
            $this->setQueryString($queryString);
        }
        $this->setStatement($this->getConnection()->prepare($this->getQueryString()));
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