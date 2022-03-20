<?php

namespace megabike\forms;

class FieldsCriteriaManager extends AbstractCriteriaManager
{
    protected $fields;
    protected $tableAlias;
    protected $subsetTableAliases;

    public function __construct($fields, $tableAlias = '')
    {
        parent::__construct();
        $this->fields = $fields;
        $this->tableAlias = $tableAlias;
        $this->subsetTableAliases = array();
    }

    /**
     * 
     * @return string
     */
    public final function getMainTableAlias()
    {
        return $this->tableAlias;
    }

    /**
     * 
     * @param string $value
     */
    public final function setMainTableAlias($value)
    {
        $this->tableAlias = (string)$value;
    }

    /**
     * 
     * @return array
     */
    public final function getSubsetTableAliases()
    {
        return $this->subsetTableAliases;
    }

    /**
     * 
     * @param array $aliasesMap
     */
    public final function setSubsetTableAliases($aliasesMap)
    {
        $this->subsetTableAliases = (array)$aliasesMap;
    }

    /**
     * 
     * @param string $subset
     * @param string $tableAlias
     */
    public final function setSubsetTableAlias($subset, $tableAlias)
    {
        $this->subsetTableAliases[$subset] = (string)$tableAlias;
    }

    /**
     * 
     * @param string $subset
     * @param string $tableAlias
     * @return string
     */
    public final function getSubsetTableAlias($subset)
    {
        if ((string)$subset === '' || !isset($this->subsetTableAliases[$subset])) {
            return $this->tableAlias;
        } else {
            return $this->subsetTableAliases[$subset];
        }
    }

    /**
     * 
     * @param string $order
     * @param FieldInterface $field
     * @return string
     */
    protected function generateOrderExpression($order, $field)
    {
        if ($field->isOrderField()) {
            return $field->getOrderExpression($order, $this->getSubsetTableAlias($field->getSubset()));
        } else {
            return null;
        }
    }

    /**
     * 
     * @param string $value
     * @param string $operator
     * @param FieldInterface $field
     * @return string
     */
    protected function generateWhereCondition($value, $operator, $field)
    {
        if ($field->isWhereField()) {
            return $field->getWhereCondition($value, $operator, $this->getSubsetTableAlias($field->getSubset()));
        } else {
            return null;
        }
    }

    /**
     * 
     * @param FieldInterface $field
     */
    protected function getFieldOperators($field)
    {
        if ($field->isWhereField()) {
            return $field->getWhereOperators();
        } else {
            return null;
        }
    }

    /**
     * 
     * @param FieldInterface $field
     */
    protected function getFieldDefaultOrder($field)
    {
        return $field->getOrderDefaultDirection();
    }

    /**
     * 
     * @param string $id
     * @return FieldInterface
     */
    public function getField($id)
    {
        return isset($this->fields[$id]) ? $this->fields[$id] : null;
    }

    /**
     * 
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

}
