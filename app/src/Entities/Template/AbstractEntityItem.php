<?php

namespace Entities\Template;

use Entities\Object;

abstract class AbstractEntityItem extends Object
{
    const ENTITY_TYPE = '';
    /**
     * Return entity key based on class name
     *
     * @return string
     */
    public function getEntityName()
    {
        $fullClassName = array_slice(explode('\\', get_class($this)), 0, -1);
        $entityKey = array_slice($fullClassName, 1, 1);
        return lcfirst(reset($entityKey));
    }

    public function getEntityType()
    {
        return static::ENTITY_TYPE;
    }
}