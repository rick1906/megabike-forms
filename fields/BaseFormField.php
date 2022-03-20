<?php

namespace megabike\forms\fields;

abstract class BaseFormField extends AbstractFormField
{

    protected $field = null;

    protected function generateDefaultError()
    {
        return tl('Поле заполнено неверно');
    }

    protected function generateRequiredError()
    {
        return tl('Обязательное поле не заполнено');
    }

    protected function writeOutputValue($formValue, $object, &$output)
    {
        if ($this->field !== null) {
            $output[$this->field] = $this->prepareOutputValue($formValue);
        }
    }

    protected function transformInputValue($value)
    {
        return $value;
    }

    protected function transformNormalValue($value)
    {
        return $value;
    }

    protected function prepareOutputValue($formValue)
    {
        return $formValue;
    }

    protected function generateInputName($parent)
    {
        if ($parent instanceof \megabike\forms\AbstractForm) {
            return $parent->generateInputName($this);
        } else {
            return $this->id;
        }
    }

    protected function generateHtmlId($parent = null, $suffix = '')
    {
        $id = $this->generateInputName($parent);
        $key = (new \ReflectionClass($this))->getShortName().'_'.$id.'_'.$suffix;
        return trim(preg_replace('/\W/', '', $key), '_');
    }

    public function getFormValue($object = null, $input = null)
    {
        if ($input && array_key_exists($this->id, $input)) {
            return $this->transformInputValue($input[$this->id]);
        }
        if ($object !== null) {
            return $this->transformNormalValue($this->getNormalValue($object));
        }
        return null;
    }

    public function generateInputHtml($parent, $object = null, $input = null)
    {
        if ($object !== null || $input !== null) {
            $value = $this->getFormValue($object, $input);
        } else {
            $value = null;
        }

        $name = $this->generateInputName($parent);

        $buffer = '';
        $buffer .= '<input type="text" name="'.$name.'"';
        $buffer .= ' value="'.html_encode($value).'"';
        if ($this->hasAttribute('maxlength')) {
            $buffer .= ' maxlength="'.$this->getAttribute('maxlength').'"';
        }
        $buffer .= ' />';
        return $buffer;
    }

    public function getNormalValue($object)
    {
        if ($this->field !== null) {
            return isset($object[$this->field]) ? $object[$this->field] : null;
        }
        return null;
    }

    public function getTextValue($object)
    {
        return (string)$this->getNormalValue($object);
    }

    public function isEmpty($formValue)
    {
        return (string)$formValue === '';
    }

    public function isRealField()
    {
        return $this->field !== null;
    }

    public function getWhereOperators()
    {
        return array('=', '%LIKE%', 'LIKE', 'LIKE%', '%LIKE', '>=', '<=', '>', '<', '<>', '!=');
    }

    public function getFieldExpression($tableAlias = '')
    {
        $field = $this->getAttribute('tableField', $this->field);
        if ($field !== null) {
            $ta = (string)$tableAlias !== '' ? ($tableAlias.'.') : '';
            return "{$ta}`{$field}`";
        } else {
            return null;
        }
    }

    public function getWhereCondition($value, $operator, $tableAlias = '')
    {
        $fe = $this->getFieldExpression($tableAlias);
        if ($fe !== null) {
            if ($operator === '%LIKE%') {
                $vs = "'%".\megabike\db\DbManager::escapeMask($value)."%'";
                $operator = 'LIKE';
            } elseif ($operator === 'LIKE%') {
                $vs = "'".\megabike\db\DbManager::escapeMask($value)."%'";
                $operator = 'LIKE';
            } elseif ($operator === '%LIKE') {
                $vs = "'%".\megabike\db\DbManager::escapeMask($value)."'";
                $operator = 'LIKE';
            } else {
                $vs = \megabike\db\DbManager::constant($value);
            }
            return "{$fe} {$operator} {$vs}";
        } else {
            return null;
        }
    }

    public function getOrderExpression($order, $tableAlias = '')
    {
        $fe = $this->getFieldExpression($tableAlias);
        if ($fe !== null) {
            return "{$fe} {$order}";
        } else {
            return parent::getOrderExpression($order, $tableAlias);
        }
    }

}
