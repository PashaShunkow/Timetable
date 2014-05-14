<?php
/**
 * DbAdapter extension for UPDATE query type
 *
 * @category  TimetableTool
 * @package   TimetableTool_System\DbAdapter
 * @author    Paul Shunkow
 * @copyright 2014 Paul Shunkow
 */
namespace System\DbAdapter;

use System\DbAdapter;

class Update extends DbAdapter
{
    const QUERY_STRING = 'UPDATE {{table}} SET {{pairs}} ';

    /**
     * Prepare query string before executing
     *
     * @return $this
     */
    public function prepareQuery()
    {
        $queryString = $this->getQueryString();
        $bindPairs   = $this->getBindPairs();
        $pairs = '';
        foreach ($bindPairs as $key => $value) {
            $pairs .= $key . self::SQL_CONDITION_EQ . ':' . $key . ', ';
        }
        $queryString = str_replace('{{pairs}}', rtrim($pairs, ', '), $queryString);
        $this->setStatement($this->getConnection()->prepare($queryString));
        return $this;
    }
}