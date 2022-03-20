<?php

namespace megabike\forms\fields;

use megabike\forms\DbTableProviderInterface;

class AutoVariantsField extends VariantsField
{

    protected $where = null;

    protected function initializeMetadata($object)
    {
        parent::initializeMetadata($object);
        if ($object instanceof DbTableProviderInterface) {
            $this->setAttribute('formDbTable', $object->getTable());
            $this->setAttribute('formDbKey', $object->getTableKey());
        }
    }

    public function validate($formValue, &$error = null)
    {
        return true;
    }

    protected function transformInputValue($value)
    {
        return (string)$value;
    }

    protected function isVariantDisplayed($value)
    {
        return true;
    }

    protected function prepareOutputValue($formValue)
    {
        if ($this->isEmpty($formValue)) {
            return $this->onEmpty;
        } else {
            return (string)$formValue;
        }
    }

    public function getTextValue($object)
    {
        return (string)$this->getNormalValue($object);
    }

    public function getAutoValues()
    {
        $values = (array)$this->values;
        $table = $this->getAttribute('formDbTable');
        if ($table !== null && $this->field !== null) {
            $where = (array)$this->where;
            $query = \megabike\db\DbManager::createSelectDistinct($this->field, $table, $where, $this->field);
            $reader = \megabike\db\DbManager::queryReader($query);
            foreach ($reader as $row) {
                $value = (string)$row[$this->field];
                $values[$value] = $value;
            }
        }
        return $values;
    }

    public function generateInputHtml($parent, $object = null, $input = null)
    {
        $values = $this->getAutoValues();
        return $this->generateInputHtmlByValues($values, $parent, $object, $input);
    }

}
