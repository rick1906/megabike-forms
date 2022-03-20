<?php

namespace megabike\forms;

abstract class FieldsManager extends FieldsBuilder
{

    /**
     *
     * @var array
     */
    protected $fields;

    /**
     *
     * @var array
     */
    protected $fieldsGroups;

    /**
     * 
     */
    public function __construct()
    {
        parent::__construct();
        $this->initialize();
    }

    /**
     * 
     */
    protected function initialize()
    {
        $this->resetFields();
    }

    /**
     * 
     */
    public function resetFields()
    {
        $this->fields = $this->constructFields();
        $this->fieldsGroups = $this->constructFieldsGroups();
    }

    /**
     * 
     * @return array
     */
    protected abstract function getFieldsDeclarations();

    /**
     * 
     * @return array
     */
    protected function getFieldsGroupsDeclarations()
    {
        return array();
    }

    /**
     * 
     * @return array
     */
    protected function constructFields()
    {
        $fields = array();
        $declarations = (array)$this->getFieldsDeclarations();
        foreach ($declarations as $id => $params) {
            $field = $this->createField($id, $params);
            if ($field !== null) {
                $fields[$field->getId()] = $field;
            }
        }
        return $fields;
    }

    /**
     * 
     * @return array
     */
    protected function constructFieldsGroups()
    {
        $fieldsGroups = array();
        $declarations = (array)$this->getFieldsGroupsDeclarations();
        foreach ($declarations as $groupId => $fieldsIds) {
            $fieldsGroups[$groupId] = array_merge(array_unique((array)$fieldsIds));
        }
        return $fieldsGroups;
    }

    /**
     * 
     * @param string $id
     * @param array $group
     * @return boolean
     */
    protected function isFieldInGroup($id, $group)
    {
        return in_array((string)$id, $group);
    }

    /**
     * 
     * @param string $id
     * @param string $groupId
     */
    public final function addFieldToGroup($id, $groupId)
    {
        if (isset($this->fieldsGroups[$groupId])) {
            if (!in_array((string)$id, $groupId)) {
                $this->fieldsGroups[$groupId][] = $id;
            }
        } else {
            $this->fieldsGroups[$groupId] = array($id);
        }
    }

    /**
     * 
     * @param array $fields
     * @param string $groupId
     * @return array
     */
    public final function selectFieldsInGroup($fields, $groupId)
    {
        if (!isset($this->fieldsGroups[$groupId])) {
            return array();
        }

        $group = $this->fieldsGroups[$groupId];
        $result = array();
        foreach ($fields as $id => $field) {
            if ($this->isFieldInGroup($id, $group)) {
                $result[$id] = $field;
            }
        }
        return $result;
    }

    /**
     * 
     * @param array $fields
     * @param string $groupId
     * @return array
     */
    public final function selectFieldsNotInGroup($fields, $groupId)
    {
        if (!isset($this->fieldsGroups[$groupId])) {
            return $fields;
        }

        $group = $this->fieldsGroups[$groupId];
        $result = array();
        foreach ($fields as $id => $field) {
            if (!$this->isFieldInGroup($id, $group)) {
                $result[$id] = $field;
            }
        }
        return $result;
    }

    /**
     * 
     * @param string $filterString
     * @return array
     */
    public function getFieldsByFilter($filterString)
    {
        $result = array();
        $blockOr = explode(',', $filterString);
        foreach ($blockOr as $part) {
            $subFilter = trim(trim($part), '&');
            if ($subFilter !== '') {
                $blockAnd = explode('&', $subFilter);
                $fields = $this->fields;
                foreach ($blockAnd as $item) {
                    $groupId = trim($item);
                    if (substr($groupId, 0, 1) === '!') {
                        $groupId = ltrim(substr($groupId, 1));
                        $fields = $this->selectFieldsInGroup($fields, $groupId);
                    } else {
                        $fields = $this->selectFieldsNotInGroup($fields, $groupId);
                    }
                }
                $result += $fields;
            }
        }
        return $result;
    }

    /**
     * 
     * @return array
     */
    public final function getFieldsGroups()
    {
        return $this->fieldsGroups;
    }

    /**
     * 
     * @return array
     */
    public final function getFields()
    {
        return $this->fields;
    }

    /**
     * 
     * @param string $id
     * @return FieldInterface
     */
    public final function getField($id)
    {
        if (isset($this->fields[$id])) {
            return $this->fields[$id];
        }
        return null;
    }

    /**
     * 
     * @return string
     */
    protected abstract function getNameFieldId();

    /**
     * 
     * @param mized $order
     */
    public function reorderFields($order)
    {
        $this->fields = FormHelper::orderFields($this->fields, $order);
    }

    /**
     * @param string $id
     * @param array $declaration
     * @return boolean
     */
    public function addField($id, $declaration)
    {
        $field = $this->createField($id, $declaration);
        if ($field !== null) {
            $this->fields[$field->getId()] = $field;
            return true;
        }
        return false;
    }

    /**
     * 
     * @param string $id
     * @return boolean
     */
    public function removeField($id)
    {
        if (isset($this->fields[$id])) {
            unset($this->fields[$id]);
            return true;
        }
        return false;
    }

    /**
     * 
     * @param mixed $storedData
     * @return string
     */
    public function getDataName($storedData)
    {
        if ($storedData instanceof FormData) {
            $name = $storedData->getName();
            if ($name !== null) {
                return $name;
            }
        }
        $fieldId = $this->getNameFieldId();
        if ($fieldId !== null) {
            $field = $this->getField($fieldId);
            if ($field) {
                return (string)$field->getTextValue($this->extractDataForField($field, $storedData));
            }
            if (isset($storedData[$fieldId])) {
                return (string)$storedData[$fieldId];
            }
        }
        return null;
    }

    /**
     * 
     * @param FieldInterface $field
     * @param mixed $storedData
     * @return mixed
     */
    protected function extractDataForField($field, $storedData)
    {
        if ($storedData !== null) {
            $subset = $field->getSubset();
            if ($subset === null) {
                return $storedData;
            } elseif ($storedData instanceof FormData) {
                return $storedData->getSubsetOrSelf($subset);
            } elseif (is_array($storedData) && array_key_exists($subset, $storedData)) {
                return $storedData[$subset];
            } else {
                return $storedData;
            }
        }
        return null;
    }

}
