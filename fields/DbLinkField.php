<?php

namespace megabike\forms\fields;

use megabike\forms\DbTableProviderInterface;
use megabike\forms\FormData;
use megabike\forms\FormHelper;
use megabike\forms\FormSettings;

class DbLinkField extends ValueBoxField
{

    protected $_dataCache = array();
    protected $_valuesCache = array();
    protected $_modelObject = null;
    //
    protected $preload = false;
    protected $excludeSelf = null;
    //
    protected $nullValue = 0;
    protected $nullText = '';
    //
    protected $select = '*';
    protected $table = null;
    protected $where = null;
    protected $order = null;
    protected $groupby = null;
    //
    protected $key = null;
    protected $text = null;
    protected $keyExpr = null;
    protected $modelClass = null;
    protected $extractor = null;
    protected $joinedColumns = null;

    protected function initializeMetadata($object)
    {
        parent::initializeMetadata($object);
        if ($object instanceof DbTableProviderInterface) {
            $this->setAttribute('formDbTable', $object->getTable());
            $this->setAttribute('formDbKey', $object->getTableKey());
        }
    }

    protected function initialize()
    {
        parent::initialize();
        $this->values = null;
        $this->onUnknown = $this->nullValue;
        $this->onEmpty = $this->nullValue;
        if ($this->modelClass !== null) {
            $tk = FormSettings::getInstance()->getTableAndKeyFromModelClass($this->modelClass);
            if ($tk) {
                if ($this->table === null) {
                    $this->table = $tk[0];
                }
                if ($this->key === null) {
                    $this->key = $tk[1];
                }
            }
        }
        if ($this->excludeSelf === null) {
            $this->excludeSelf = $this->hasAttribute('formDbTable') && FormHelper::tableEquals($this->getAttribute('formDbTable'), $this->table);
        }
        if ($this->keyExpr === null) {
            $this->keyExpr = "{$this->table}.{$this->key}";
        }
        if ($this->groupby === null && isset($this->_attributes['groupBy'])) {
            $this->groupby = $this->_attributes['groupBy'];
        }
        if ($this->preload) {
            $this->getValues();
        }
    }

    protected function getValues()
    {
        if ($this->values !== null) {
            return $this->values;
        }

        $this->values = array((string)$this->nullValue => $this->nullText);

        $this->_valuesCache = array();
        $query = \megabike\db\DbManager::createSelect($this->select, $this->table, $this->where, $this->order, null, $this->groupby);
        $res = \megabike\db\DbManager::query($query);
        while ($row = \megabike\db\DbManager::fetch($res)) {
            $text = $this->createOptionText($row);
            $this->values[$row[$this->key]] = $text;
            $this->cacheItemData($row[$this->key], $text, $row, true);
        }

        return $this->values;
    }

    protected function loadJoinedItemData($id, $object, $map)
    {
        $row = array();
        if ($object instanceof FormData) {
            $item = $object->toArray();
        } elseif (is_array($object)) {
            $item = $object;
        } else {
            return null;
        }
        foreach ((array)$map as $k => $v) {
            if (is_int($k)) {
                $k = $v;
            }
            if (array_key_exists($k, $item)) {
                $row[$v] = $item[$k];
            }
        }
        if (!isset($row[$this->key]) || (int)$row[$this->key] !== (int)$id) {
            return null;
        }
        return $row;
    }

    protected function loadItemData($id, $strict = false, $object = null)
    {
        if ($this->joinedColumns !== null && $object !== null && !$strict) {
            $row = $this->loadJoinedItemData($id, $object, $this->joinedColumns);
            if ($row !== null) {
                return $row;
            }
        }
        $where = $strict ? (array)$this->where : array();
        $where[$this->keyExpr] = (int)$id;
        $query = \megabike\db\DbManager::createSelect($this->select, $this->table, $where, $this->order, null, $this->groupby);
        return \megabike\db\DbManager::queryRow($query);
    }

    protected function getNameForId($id, $object = null)
    {
        $key = (int)$id;
        if ($this->values !== null && isset($this->values[$key])) {
            return $this->values[$key];
        } elseif ($this->_valuesCache && array_key_exists($key, $this->_valuesCache)) {
            return $this->_valuesCache[$key];
        }

        $row = $this->loadItemData($id, false, $object);
        if ($row) {
            $text = $this->createOptionText($row);
        } else {
            $text = $this->nullText;
        }

        $this->_valuesCache[$key] = $text;
        $this->cacheItemData($id, $text, $row);
        return $text;
    }

    protected function cacheItemData($id, $text, $row, $fullLoad = false)
    {
        $key = (int)$id;
        $this->_dataCache[$key] = [$row, $text, $fullLoad];
    }

    protected function getItemData($id, $object = null)
    {
        $key = (int)$id;
        if (isset($this->_dataCache[$key])) {
            return $this->_dataCache[$key];
        }
        if (isset($this->_valuesCache[$key])) {
            return null; // if values cache exists, then data is already loaded
        }

        $this->getNameForId($key, $object);
        return isset($this->_dataCache[$key][0]) ? $this->_dataCache[$key][0] : null;
    }

    protected function createOptionText($row)
    {
        if ($this->extractor !== null) {
            $text = $this->extractor->extractName(new FormData($row, $this->extractor));
            if ($text !== null) {
                return $text;
            }
        }
        if (strpos($this->text, '{') === false) {
            $text = isset($row[$this->text]) ? $row[$this->text] : null;
        } else {
            $text = FormHelper::processPattern($this->text, $row);
        }
        if ($text === null) {
            $text = isset($row[$this->key]) ? '[ '.$row[$this->key].' ]' : null;
        }
        return $text;
    }

    public function isOrderField()
    {
        return $this->isRealField() && $this->getAttribute('isOrder', false) || $this->orderParams !== null;
    }

    public function validate($formValue, &$error = null)
    {
        if ($this->isEmpty($formValue)) {
            return true;
        }
        if ($this->values !== null && isset($this->values[$formValue])) {
            return true;
        } elseif ($this->loadItemData($formValue, true)) {
            return true;
        } else {
            $error = tl('Выбран неизвестный вариант');
            return false;
        }
    }

    protected function prepareOutputValue($formValue)
    {
        if ($this->isEmpty($formValue)) {
            return $this->onEmpty;
        } else {
            return (int)$formValue;
        }
    }

    public function getTextValue($object)
    {
        if ($this->field !== null) {
            $value = $this->getNormalValue($object);
            if ($value !== null) {
                $text = $this->getNameForId($value, $object);
                if ($text === null) {
                    return $this->nullText;
                } else {
                    return $text;
                }
            }
        }
        return '';
    }

    public function generateInputHtml($parent, $object = null, $input = null)
    {
        $values = $this->getValues();
        if ($this->excludeSelf && !empty($object) && $this->hasAttribute('formDbKey')) {
            $key = $this->getAttribute('formDbKey');
            if (isset($object[$key])) {
                unset($values[$object[$key]]);
            }
        }
        if ($object !== null || $input !== null) {
            $value = $this->getFormValue($object, $input);
            if (is_scalar($value) && !isset($values[$value]) && is_numeric($value) && (string)$value !== (string)$this->onEmpty) {
                $values[$value] = $this->getNameForId($value, $object);
            }
        }
        return $this->generateInputHtmlByValues($values, $parent, $object, $input);
    }

}
