<?php
/**
 * DbAdapter extension for DELETE query type
 *
 * @category  TimetableTool
 * @package   TimetableTool_System\DbAdapter
 * @author    Paul Shunkow
 * @copyright 2014 Paul Shunkow
 */
namespace System\DbAdapter;

use System\DbAdapter;

class Delete extends DbAdapter
{
    const QUERY_STRING = 'DELETE FROM {{table}} ';

    /**
     * Prepare query string before executing
     *
     * @return $this
     */
    public function prepareQuery()
    {
        $this->setStatement($this->getConnection()->prepare($this->getQueryString()));
        return $this;
    }
}