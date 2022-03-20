<?php

namespace megabike\forms;

class FormTableFilter extends FieldsBuilder
{

    /**
     *
     * @var CriteriaManagerInterface
     */
    protected $criteriaManager;

    /**
     *
     * @var array
     */
    protected $items = array();

    /**
     *
     * @var array
     */
    protected $inputs = array();

    /**
     *
     * @var mixed
     */
    protected $_values = null;

    /**
     *
     * @var mixed
     */
    protected $_where = null;

    /**
     * 
     * @param CriteriaManagerInterface $criteriaManager
     * @param array $filters
     */
    public function __construct($criteriaManager, $filters = array())
    {
        $this->criteriaManager = $criteriaManager;
        $this->registerFilters($filters);
        parent::__construct();
    }

    public function isActive()
    {
        return !empty($this->getWhere());
    }

    public function reset()
    {
        $this->_values = null;
        $this->_where = null;
    }

    public final function getWhere()
    {
        if ($this->_where === null) {
            $this->_where = $this->buildWhere();
        }
        return $this->_where;
    }

    public final function getValues()
    {
        if ($this->_values === null) {
            $this->_values = $this->buildValues();
        }
        return $this->_values;
    }

    public function setValues($input)
    {
        $this->_values = array();
        $fields = $this->generateFilterFields();
        foreach ($fields as $id => $field) {
            $value = $field->getFormValue(null, $input);
            if (!$field->isEmpty($value)) {
                $this->_values[$id] = $value;
            } else {
                $this->_values[$id] = null;
            }
        }
    }

    public function registerFilters($filters)
    {
        foreach ($filters as $id => $params) {
            $this->registerFilter($id, $params);
        }
    }

    public function registerFilter($id, $params = array())
    {
        $p = $this->prepareFilterParams($id, $params);
        $map = isset($p['map']) ? $p['map'] : array();
        $values = isset($p['values']) ? $p['values'] : array();
        $operator = isset($p['operator']) ? $p['operator'] : null;
        if ($values) {
            foreach (array_keys($values) as $k) {
                if (!isset($map[$k])) {
                    $map[$k] = $k;
                }
            }
        }
        if (!empty($p['autoEmpty'])) {
            $empk = '__empty_'.md5($id);
            $values[''] = '';
            $values[$empk] = (string)$p['autoEmpty'];
            $map[$empk] = '';
        }
        if ($p['type'] === 'multi') {
            $this->addMultiFilter($id, $map);
            $this->setVariantsInput($id, $p, $values);
        } elseif ($p['type'] === 'variants') {
            $this->addMappedFilter($id, $p['fieldId'], $operator, $map);
            $this->setVariantsInput($id, $p, $values);
        } elseif ($p['type'] === 'autoVariants') {
            $this->addMappedFilter($id, $p['fieldId'], $operator, $map);
            $this->setAutoVariantsInput($id, $p, $values);
        } else {
            if (isset($p['map'])) {
                $this->addMappedFilter($id, $p['fieldId'], $operator, $map);
            } else {
                $this->addFilter($id, $p['fieldId'], $operator);
            }
            if ($p['type'] === 'custom') {
                $this->setCustomInput($id, $params);
            } else {
                $this->setInput($id, $params);
            }
        }
    }

    protected function prepareFilterParams($id, $params)
    {
        if (isset($params[0]) && !isset($params['fieldId'])) {
            $params['fieldId'] = $params[0];
        }
        if (isset($params[1]) && !isset($params['type'])) {
            $params['type'] = null;
        }
        if (!isset($params['fieldId'])) {
            $params['fieldId'] = $id;
        }
        if (!isset($params['type'])) {
            $params['type'] = null;
        }
        if (empty($params['type']) && isset($params['values'])) {
            $params['type'] = 'variants';
        }
        return $params;
    }

    public function addFilter($id, $fieldId = null, $operator = null)
    {
        if ((string)$operator === '') {
            $operator = '=';
        }
        if ((string)$fieldId === '') {
            $fieldId = $id;
        }
        $this->items[$id] = array($fieldId, strtoupper($operator));
    }

    public function addMappedFilter($id, $fieldId = null, $operator = null, $map = array())
    {
        if ((string)$operator === '') {
            $operator = '=';
        }
        if ((string)$fieldId === '') {
            $fieldId = $id;
        }
        $this->items[$id] = array($fieldId, strtoupper($operator), (array)$map);
    }

    public function addMultiFilter($id, $map = array())
    {
        $this->items[$id] = array((array)$map);
    }

    public function setInput($id, $params = array())
    {
        $this->inputs[$id] = (array)$params;
    }

    public function setVariantsInput($id, $params = array(), $values = array())
    {
        $this->inputs[$id] = (array)$params;
        $this->inputs[$id]['type'] = 'variants';
        $this->inputs[$id]['values'] = (array)$values;
    }

    public function setAutoVariantsInput($id, $params = array(), $values = array())
    {
        $this->inputs[$id] = (array)$params;
        $this->inputs[$id]['type'] = 'autoVariants';
        $this->inputs[$id]['values'] = (array)$values;
    }

    public function setCustomInput($id, $params = array())
    {
        $this->inputs[$id] = (array)$params;
        $this->inputs[$id]['type'] = 'custom';
    }

    public function generateFilterFields()
    {
        $fields = array();
        foreach (array_keys($this->items) as $id) {
            $field = $this->generateFilterField($id);
            if ($field) {
                $fields[$id] = $field;
            }
        }
        return $fields;
    }

    public function generateFilterField($id)
    {
        $item = isset($this->items[$id]) ? $this->items[$id] : null;
        $params = isset($this->inputs[$id]) ? $this->inputs[$id] : array();
        if (!$item) {
            return null;
        }

        unset($params['id']);
        unset($params['operator']);
        unset($params['map']);
        $params['required'] = false;
        $params['field'] = $id;
        $params['onUnknown'] = '';
        $params['onEmpty'] = '';

        $type = isset($params['type']) ? $params['type'] : 'string';
        if ($type === 'custom') {
            $manager = $this->criteriaManager;
            if ($manager instanceof AbstractCriteriaManager && !is_array($item[0])) {
                $fieldId = $item[0];
                $field = $manager->getField($fieldId);
                if ($field && $field instanceof FormFieldInterface) {
                    return $this->generateCustomFilterField($id, $params, $field);
                }
            }
            return null;
        }

        $params['type'] = $type;
        return $this->createField($id, $params);
    }

    protected function generateCustomFilterField($id, $params, $field)
    {
        $filterField = clone $field;
        $filterField->setAttribute('id', $id);
        foreach ($params as $k => $v) {
            $filterField->setAttribute($k, $v);
        }
        return $filterField;
    }

    protected function valuesEqual($value1, $value2)
    {
        if (is_array($value1)) {
            return is_array($value2) && $value1 === $value2;
        } else {
            return (string)$value1 === (string)$value2;
        }
    }

    protected function operatorAndValue($container)
    {
        if (is_array($container)) {
            return array(strtoupper($container[0]), $container[1]);
        } else {
            return array('=', $container);
        }
    }

    protected function buildValue($filterOperator, $operator, $value)
    {
        if ($operator === $filterOperator) {
            return $value;
        } else {
            return null;
        }
    }

    protected function buildMappedValue($map, $filterOperator, $operator, $value)
    {
        foreach ($map as $filterValue => $opv) {
            if (is_array($opv)) {
                list($op, $v) = $opv;
            } else {
                $op = $filterOperator;
                $v = $opv;
            }
            if ($op === $operator && $this->valuesEqual($v, $value)) {
                return $filterValue;
            }
        }
        if (is_scalar($value) && (string)$value !== '') {
            return $this->buildValue($filterOperator, $operator, $value);
        }
        return null;
    }

    protected function buildMultiValue($map, $where)
    {
        foreach ($map as $filterValue => $params) {
            $check = true;
            if (!is_array($params)) {
                continue;
            }
            foreach ($params as $fieldId => $opv) {
                if (isset($where[$fieldId])) {
                    list($operator, $value) = $this->operatorAndValue($where[$fieldId]);
                    list($op, $v) = $this->operatorAndValue($opv);
                    $check = $check && ($op === $operator && $this->valuesEqual($v, $value));
                } else {
                    $check = false;
                    break;
                }
            }
            if ($check) {
                return $filterValue;
            }
        }
        return null;
    }

    protected function buildValues()
    {
        $where = $this->criteriaManager->getWhereFilter();
        $values = array();
        foreach ($this->items as $id => $item) {
            if (is_array($item[0])) {
                $values[$id] = $this->buildMultiValue($item[0], $where);
            } else {
                $fieldId = $item[0];
                if (isset($where[$fieldId])) {
                    list($op, $v) = $this->operatorAndValue($where[$fieldId]);
                    if (isset($item[2])) {
                        $values[$id] = $this->buildMappedValue($item[2], $item[1], $op, $v);
                    } else {
                        $values[$id] = $this->buildValue($item[1], $op, $v);
                    }
                } else {
                    $values[$id] = null;
                }
            }
        }
        return $values;
    }

    protected function buildWhere()
    {
        $where = array();
        foreach ($this->getValues() as $id => $value) {
            if ($value !== null && isset($this->items[$id])) {
                $item = $this->items[$id];
                if (is_array($item[0])) {
                    $map = $item[0];
                    if (isset($map[$value]) && is_array($map[$value])) {
                        foreach ($map[$value] as $fieldId => $opv) {
                            $where[$fieldId] = $opv;
                        }
                    }
                } else {
                    $fieldId = $item[0];
                    $operator = $item[1];
                    if (isset($item[2])) {
                        $map = $item[2];
                        if (isset($map[$value])) {
                            $opv = $map[$value];
                            if (is_array($opv) || $operator === '=') {
                                $where[$fieldId] = $opv;
                            } else {
                                $where[$fieldId] = array($operator, $opv);
                            }
                        } elseif (is_scalar($value) && (string)$value !== '') {
                            $where[$fieldId] = $operator === '=' ? $value : array($operator, $value);
                        }
                    } else {
                        $where[$fieldId] = $operator === '=' ? $value : array($operator, $value);
                    }
                }
            }
        }
        return $where;
    }

}
