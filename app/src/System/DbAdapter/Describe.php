<?php
/**
 * DbAdapter extension for DESCRIBE query type
 *
 * @category  TimetableTool
 * @package   TimetableTool_System\DbAdapter
 * @author    Paul Shunkow
 * @copyright 2014 Paul Shunkow
 */
namespace System\DbAdapter;

use System\DbAdapter;

class Describe extends DbAdapter
{
    const QUERY_STRING = 'DESCRIBE {{table}}';

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
        $this->setStatement($this->getConnection()->prepare($this->getQueryString()));
        return $this;
    }
}