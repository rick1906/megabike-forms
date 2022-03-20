<?php

namespace megabike\forms;

use RuntimeException;

abstract class AbstractDbForm extends AbstractSavableForm implements DbTableProviderInterface
{

    /**
     *
     * @var FieldsCriteriaManager
     */
    protected $criteriaManager;

    /**
     * 
     */
    protected function initialize()
    {
        parent::initialize();
        $this->criteriaManager = $this->constructCriteriaManager();
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
     * @param FieldsCriteriaManager $manager
     */
    protected function initializeQuery($manager)
    {
        $manager->setQuery('*', $this->getTable());
    }

    /**
     * 
     * @param mixed $id
     * @return string
     */
    protected function getQuery($id)
    {
        return $this->criteriaManager->getSingleRecordQuery($this->getKeyCondition($id, true));
    }

    /**
     * 
     * @param mixed $id
     * @param boolean $withAlias
     * @return mixed
     */
    protected function getKeyCondition($id, $withAlias = false)
    {
        if ($withAlias) {
            $tableAlias = $this->getTableAlias();
            if ((string)$tableAlias !== '') {
                $fieldString = $tableAlias.'.'.$this->getTableKey();
            } else {
                $fieldString = $this->getTableKey();
            }
            return array($fieldString => $id);
        } else {
            return array($this->getTableKey() => $id);
        }
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
     * @return FormData
     */
    protected function loadStoredData()
    {
        $query = $this->getQuery($this->dataId);
        $result = \megabike\db\DbManager::query($query);
        if ($result) {
            $row = \megabike\db\DbManager::fetch($result);
            return $row ? $this->transformDbData($row) : null;
        } else {
            throw new RuntimeException("Database query failed");
        }
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
     * @return type
     */
    public function getCurrentName()
    {
        $data = $this->getStoredOrDefaultData();
        return $data ? $this->getDataName($data) : '';
    }

    /**
     * 
     * @param FormData $storedData
     * @return string
     */
    public function getDataName($storedData)
    {
        $name = parent::getDataName($storedData);
        if ($name === null) {
            return $storedData[$this->getTableKey()];
        }
        return $name;
    }

    /**
     * 
     * @param string $baseUrl
     * @param boolean $keepQuery
     * @param array $params
     * @return string
     */
    public function generateUrl($baseUrl, $keepQuery = true, $params = array())
    {
        return $this->criteriaManager->generateUrl($baseUrl, $keepQuery, $params, false);
    }

    /**
     * 
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->criteriaManager->setBaseUrl($baseUrl);
    }

    /**
     * 
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->criteriaManager->getBaseUrl();
    }

    /**
     * 
     * @param array $data
     * @return mixed
     */
    protected function saveToStorage($data)
    {
        if ($this->scenario === self::SCENARIO_ADD) {
            return $this->saveAdd($data);
        }
        if ($this->scenario === self::SCENARIO_EDIT) {
            return $this->saveEdit($data);
        }
        if ($this->scenario === self::SCENARIO_DELETE) {
            return $this->saveDelete();
        }
        return false;
    }

    /**
     * 
     * @param array $data
     * @return array
     */
    protected function filterQueryInput($data)
    {
        $result = array();
        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * 
     * @param array $data
     * @return mixed
     */
    protected function saveAdd($data)
    {
        $queryData = $this->filterQueryInput($data);
        $query = \megabike\db\DbManager::createInsert($this->getTable(), $queryData);
        $result = \megabike\db\DbManager::query($query);
        return $result ? \megabike\db\DbManager::insertId() : false;
    }

    /**
     * 
     * @param array $data
     * @return mixed
     */
    protected function saveEdit($data)
    {
        $queryData = $this->filterQueryInput($data);
        $query = \megabike\db\DbManager::createUpdate($this->getTable(), $queryData, $this->getKeyCondition($this->dataId));
        return (bool)\megabike\db\DbManager::query($query);
    }

    /**
     * 
     * @param array $data
     * @return mixed
     */
    protected function saveDelete()
    {
        $query = \megabike\db\DbManager::createDelete($this->getTable(), $this->getKeyCondition($this->dataId));
        return (bool)\megabike\db\DbManager::query($query);
    }

}
