<?php
/**
 * Abstract collection class
 *
 * @category  TimetableTool
 * @package   TimetableTool_Entities\Template
 * @author    Paul Shunkow
 * @copyright 2014 Paul Shunkow
 */
namespace Entities\Template;

use System\DbAdapter;

use \App;

abstract class AbstractCollection extends DbAdapter implements \Iterator
{
    const ENTITY_TYPE   = 'collection';

    protected $_pointer = 0;

    protected $_items   = array();
    protected $_model   = null;

    protected $_fieldsToSelect = self::SQL_INCLUDE_ALL;
    protected $_fieldsToFilter = array();

    public function __construct(AbstractModel $model)
    {
        $this->_pointer = 0;
        $this->_model   = $model;
    }

    /**
     * Add fields to SQL Select
     *
     * @param  array $fields Fields in table
     * @return $this
     */
    public function addFieldToSelect($fields)
    {
        if ($fields == self::SQL_INCLUDE_ALL) {
            $this->_fieldsToFilter = $fields;
            return $this;
        }

        if (!is_array($fields)) {
            $fields = array($fields);
        }

        if (is_array($this->_fieldsToSelect) && !empty($this->_fieldsToSelect)) {
            $fields = array_merge($fields, $this->_fieldsToSelect);
        }

        $this->_fieldsToSelect = $fields;
        return $this;
    }

    /**
     * Add fields to collection filter
     *
     * @param array  $pairs key => value pairs
     * @param string $condition eq, neq and etc.
     *
     * @return $this
     */
    public function addFieldToFilter(array $pairs, $condition = self::SQL_CONDITION_EQ)
    {
        $this->_fieldsToFilter[] = array('pairs' => $pairs, 'condition' => $condition);
        return $this;
    }

    /**
     * Add filters to collection query
     *
     * @param DbAdapter\Select $select
     */
    protected function _applyCollectionFilters(DbAdapter\Select $select)
    {
        if (!empty($this->_fieldsToFilter)) {
            foreach ($this->_fieldsToFilter as $filter) {
                $select->where($filter['pairs'], $filter['condition']);
            }
        }
    }

    /**
     * Add entity_id key into fields array(if need it), return fields array
     *
     * @return array||string
     */
    protected function _getFieldsToSelect()
    {
        if($this->_fieldsToSelect != self::SQL_INCLUDE_ALL && !in_array($this->getCoreModel()->getEntityPrimaryKey(), $this->_fieldsToSelect))
        {
            $this->_fieldsToSelect[] = $this->getCoreModel()->getEntityPrimaryKey();
        }
        return $this->_fieldsToSelect;
    }

    /**
     * Return inner core model
     *
     * @return AbstractModel
     */
    public function getCoreModel()
    {
        return $this->_model;
    }

    /**
     * Common load method for collections
     *
     * @param  bool  $initForeignModels
     * @return $this
     */
    public function load($initForeignModels = true)
    {
        try {
            $dbAdapter = $this->getCoreModel()->getDbAdapter();
            /** @var \System\DbAdapter\Select $select */
            $select = $dbAdapter->defineCurrentAction('select');
            $select->setTable($this->getCoreModel()->getEntityTable());
            $select->addToSelect($this->_getFieldsToSelect());
            $this->_applyCollectionFilters($select);
            $select->prepareQuery();
            if ($select->execute()) {
                $result = $select->fetchAll(\PDO::FETCH_ASSOC);
                $this->_fillCollectionData($result, $initForeignModels);
                $this->setIsLoaded(true);
                return $this;
            } else {
                App::error('Cant load collection of: ' . $this->getCoreModel()->getEntityName());
            }
        } catch (\PDOException $e) {
            App::error('Cant load collection: ' . $e->getMessage());
        }
    }

    /**
     * Set results into inner array
     *
     * @param  bool  $initForeignModels
     * @param  array $result Query results
     * @return $this
     */
    protected function _fillCollectionData($result, $initForeignModels)
    {
        foreach ($result as $key => $data) {
            $item = clone $this->getCoreModel();
            $item->fillModelData($data, $initForeignModels);
            $this->_items[$key] = $item;
        }

        return $this;
    }

    /**
     * Return count of loaded items
     *
     * @return int
     */
    public function count()
    {
        if (!$this->_isLoaded()) {
            $this->load();
        }
        return count($this->_items);
    }

    /**
     * Return options array based on loaded items
     *
     * @param  string $label Here you can specify the key which will be a label
     * @return array
     */
    public function toOptionArray($label = 'name')
    {
        $optionsArray = array();
        foreach ($this->getItems() as $item) {
            $optionsArray[] = array('value' => $item->getEntityId(), 'label' => $item->getData($label), 'selected' => null);
        }
        return $optionsArray;
    }

    /**
     * Return array of loaded items
     *
     * @return array
     */
    public function getItems()
    {
        if (!$this->_isLoaded()) {
            $this->load();
        }
        return $this->_items;
    }

    /**
     * Return items from collection by id value
     *
     * @param $ids
     * @return array
     */
    public function getItemsByIds($ids)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $outItems = array();
        $items = $this->getItems();
        foreach ($items as $item) {
            foreach ($ids as $id) {
                if ($item->getEntityId() == $id) {
                    $outItems[] = clone $item;
                }
            }
        }
        return $outItems;
    }

    /**
     * Check is collection is already loaded
     *
     * @return bool
     */
    protected function _isLoaded()
    {
        return $this->getIsLoaded();
    }

    public function rewind() {
        if (!$this->_isLoaded()) {
            $this->load();
        }
        $this->_pointer = 0;
    }

    public function current() {
        return $this->_items[$this->_pointer];
    }

    public function key() {
        return $this->_pointer;
    }

    public function next() {
        ++$this->_pointer;
    }

    public function valid() {
        return isset($this->_items[$this->_pointer]);
    }
}