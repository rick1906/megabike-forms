<?php

namespace megabike\forms;

abstract class AbstractDbFormTable extends AbstractFormTable implements DbTableProviderInterface
{

    /**
     *
     * @var FieldsCriteriaManager
     */
    protected $criteriaManager;

    /**
     *
     * @var DbDataReader
     */
    protected $_dataReader = null;

    /**
     *
     * @var mixed
     */
    protected $_buffer = null;

    /**
     * 
     * @return FieldsCriteriaManager
     */
    protected function constructCriteriaManager()
    {
        $manager = new FieldsCriteriaManager($this->getFields(), $this->getTableAlias());
        $this->initializeQuery($manager);
        return $manager;
    }

    /**
     * 
     * @return DbDataReader
     */
    protected function constructDataReader()
    {
        return new DbDataReader($this->criteriaManager->getQuery());
    }

    /**
     * 
     * @return DbDataReader
     */
    protected final function getDataReader()
    {
        if ($this->_dataReader === null) {
            $this->_dataReader = $this->constructDataReader();
        }
        return $this->_dataReader;
    }

    /**
     * 
     * @return FieldsCriteriaManager
     */
    public final function getCriteriaManager()
    {
        return $this->criteriaManager;
    }

    /**
     * 
     * @param array $dbRow
     * @return FormData
     */
    protected function transformDbData($dbRow)
    {
        return new FormData($dbRow);
    }

    /**
     * 
     * @param FieldsCriteriaManager $manager
     */
    protected function initializeQuery($manager)
    {
        $manager->setQuery('*', $this->getTable());
    }

    /**
     * 
     * @return string
     */
    public abstract function getTable();

    /**
     * 
     * @return string
     */
    public abstract function getTableKey();

    /**
     * 
     * @return string
     */
    public function getTableAlias()
    {
        return $this->getTable();
    }

    /**
     * 
     * @return int
     */
    public function getCurrentItemIndex()
    {
        if (isset($this->_buffer['current'])) {
            return $this->_buffer['current'];
        } else {
            return $this->getDataReader()->key();
        }
    }

    /**
     * 
     */
    public function enableItemsBuffer()
    {
        if (!isset($this->_buffer['items'])) {
            $this->_buffer['items'] = array();
        }
    }

    /**
     */
    public function disableItemsBuffer()
    {
        $this->_buffer = null;
    }

    /**
     * 
     * @return FormData
     */
    protected function readCurrentItem()
    {
        $row = $this->getDataReader()->current();
        $index = $this->_dataReader->key();
        if ($row !== null) {
            $item = $this->transformDbData($row);
            if (isset($this->_buffer['items'])) {
                $this->_buffer['items'][$index] = $item;
                $this->_buffer['current'] = $index;
            }
            return $item;
        }
        return null;
    }

    /**
     * 
     * @return boolean
     */
    public function rewindItemsBuffer()
    {
        if (isset($this->_buffer['items'])) {
            $this->_buffer['current'] = 0;
            return count($this->_buffer['items']);
        }
        return false;
    }

    /**
     * 
     * @return FormData
     */
    public function getCurrentItem()
    {
        if (isset($this->_buffer['current']) && $this->_buffer['current'] < count($this->_buffer['items'])) {
            $index = $this->_buffer['current'];
            return $this->_buffer['items'][$index];
        }
        return $this->readCurrentItem();
    }

    /**
     * 
     * @return FormData
     */
    public function getNextItem()
    {
        if (isset($this->_buffer['current']) && $this->_buffer['current'] < count($this->_buffer['items'])) {
            $index = ++$this->_buffer['current'];
            if (isset($this->_buffer['items'][$index])) {
                return $this->_buffer['items'][$index];
            }
        }
        if ($this->getDataReader()->valid()) {
            $this->getDataReader()->next();
            return $this->readCurrentItem();
        } else {
            return null;
        }
    }

    /**
     * 
     * @param int $index
     * @return FormData
     */
    public function getItem($index)
    {
        if (isset($this->_buffer['items'])) {
            do {
                if (isset($this->_buffer['items'][$index])) {
                    return $this->_buffer['items'][$index];
                }
            } while ($this->getNextItem());
            return null;
        } else {
            return false;
        }
    }

    /**
     * 
     * @return array
     */
    public function getItems()
    {
        if (isset($this->_buffer['items']) && !$this->getDataReader()->valid()) {
            return $this->_buffer['items'];
        } else {
            $items = array();
            $this->rewindItemsBuffer();
            while ($item = $this->getNextItem()) {
                $items[] = $item;
            }
            return $items;
        }
    }

}
