<?php

namespace megabike\forms\fields;

use megabike\forms\DbTableProviderInterface;

class AliasField extends StringField
{

    protected $strict = false;
    protected $allowSymbols = null;
    protected $unique = false;
    protected $where = null;

    protected function initialize()
    {
        parent::initialize();
        if ($this->allowSymbols === null) {
            $this->allowSymbols = $this->strict ? false : '-_.';
        }
    }

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
        if (!parent::validate($formValue, $error)) {
            return false;
        }
        if (strpos($formValue, '/') !== false || strpos($formValue, '\\') !== false) {
            $error = tl('Строка не должна содержать символы "/" и "\\"');
            return false;
        }
        if (preg_match('/\s+/', $formValue)) {
            $error = tl('Строка не должна содержать пробельных символов');
            return false;
        }
        if ((string)$formValue !== '') {
            if (!preg_match('/[a-zA-Z]+/', $formValue)) {
                $error = tl('Строка должна содержать хотя бы одну латинскую букву');
                return false;
            }
            if ($this->strict && !preg_match('/^[a-zA-Z0-9_\-]+$/', $formValue)) {
                $error = tl('Допустимы только латинские буквы, цифры, дефис и нижнее подчёркивание');
                return false;
            }
            if ($this->allowSymbols !== false && !preg_match('/^[a-zA-Z0-9'.preg_quote($this->allowSymbols, '/').']+$/', $formValue)) {
                $error = "Допустимы только латинские буквы, цифры и символы '".$this->allowSymbols."'";
                return false;
            }
        }
        return true;
    }

    public function processInput($object, $input, &$value, &$error = null)
    {
        if (parent::processInput($object, $input, $value, $error)) {
            if ($this->unique && $this->field !== null) {
                $table = $this->getAttribute('formDbTable');
                $key = $this->getAttribute('formDbKey');
                $changed = !isset($object[$this->field]) || (string)$value !== (string)$object[$this->field];
                if ($table !== null && $key !== null && $changed) {
                    $where = (array)$this->where;
                    $where[$this->field] = $value;
                    $query = \megabike\db\DbManager::createSelect([$this->field, $key], $table, $where);
                    $row = \megabike\db\DbManager::queryRow($query);
                    $currentId = isset($object[$key]) ? $object[$key] : null;
                    $targetId = isset($row[$key]) ? $row[$key] : null;
                    if ($row && ($targetId === null || (string)$currentId !== (string)$targetId)) {
                        $error = "Значение должно быть уникальным, а введённое значение уже используется";
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    }

}
