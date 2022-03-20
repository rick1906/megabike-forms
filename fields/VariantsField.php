<?php

namespace megabike\forms\fields;

class VariantsField extends BaseFormField
{

    protected $values = array();
    protected $hideValues = null;
    protected $onUnknown = null;
    protected $onEmpty = null;
    protected $display = null;
    protected $valuesSource = null;

    protected function initialize()
    {
        parent::initialize();
        if ($this->hideValues) {
            foreach ($this->hideValues as $i => $value) {
                $this->hideValues[$i] = (string)$value;
            }
        }
        if ($this->valuesSource !== null && is_callable($this->valuesSource)) {
            $this->values = call_user_func($this->valuesSource);
        }
    }

    public function isEmpty($formValue)
    {
        return $formValue === null || $formValue === $this->onEmpty && !isset($this->values[$this->onEmpty]);
    }

    public function validate($formValue, &$error = null)
    {
        if ($this->isEmpty($formValue)) {
            return true;
        }
        if (isset($this->values[$formValue])) {
            return true;
        } else {
            $error = tl('Выбран неизвестный вариант');
            return false;
        }
    }

    protected function transformInputValue($value)
    {
        return (string)$value;
    }

    protected function isVariantDisplayed($value)
    {
        return isset($this->values[$value]) && (!$this->hideValues || !in_array((string)$value, $this->hideValues));
    }

    protected function prepareOutputValue($formValue)
    {
        if ($this->isEmpty($formValue)) {
            return $this->onEmpty;
        } elseif (isset($this->values[$formValue])) {
            return (string)$formValue;
        } else {
            return $this->onUnknown;
        }
    }

    public function getTextValue($object)
    {
        $value = $this->getNormalValue($object);
        if ($value !== null) {
            if (isset($this->values[$value])) {
                return $this->values[$value];
            }
            if ($this->onUnknown !== null && isset($this->values[$this->onUnknown])) {
                return $this->values[$this->onUnknown];
            }
        }
        return '';
    }

    public function generateInputHtml($parent, $object = null, $input = null)
    {
        return $this->generateInputHtmlByValues($this->values, $parent, $object, $input);
    }

    public function generateInputHtmlByValues($values, $parent, $object = null, $input = null)
    {
        if ($object !== null || $input !== null) {
            $value = $this->getFormValue($object, $input);
        } else {
            $value = null;
        }

        if ($value === null || !isset($values[$value])) {
            $value = $this->onUnknown;
        }

        $name = $this->generateInputName($parent);
        if ($this->display === 'radio') {
            return $this->generateInputHtmlRadio($name, $value, (array)$values);
        } else {
            return $this->generateInputHtmlDefault($name, $value, (array)$values);
        }
    }

    protected function generateInputHtmlDefault($name, $value, $values)
    {
        $buffer = '';
        $buffer .= '<select name="'.$name.'">';
        if (!$this->isRequired() && !isset($values[$this->onEmpty])) {
            $buffer .= '<option value="'.html_encode($this->onEmpty).'"></option>';
        }
        foreach ($values as $k => $v) {
            if (!$this->isVariantDisplayed($k) && (string)$k !== (string)$value) {
                continue;
            }
            $buffer .= '<option value="'.html_encode($k).'"';
            if ($value !== null && (string)$k === (string)$value) {
                $buffer .= ' selected';
            }
            $buffer .= '>'.html_encode($v).'</option>';
        }
        $buffer .= '</select>';
        return $buffer;
    }

    protected function generateInputHtmlRadio($name, $value, $values)
    {
        $buffer = '';
        $buffer .= '<div>';
        if (!$this->isRequired() && !isset($values[$this->onEmpty])) {
            $buffer .= '<input type="radio" name="'.$name.'" value="'.html_encode($this->onEmpty).'"';
            if ((string)$value === (string)$this->onEmpty) {
                $buffer .= ' checked';
            }
            $buffer .= ' style="display:none;" />';
        }
        foreach ($values as $k => $v) {
            if (!$this->isVariantDisplayed($k) && (string)$k !== (string)$value) {
                continue;
            }
            $buffer .= '<label class="forms-radio-label">';
            $buffer .= '<input type="radio" name="'.$name.'" value="'.html_encode($k).'"';
            if ($value !== null && (string)$k === (string)$value) {
                $buffer .= ' checked';
            }
            $buffer .= ' />';
            $buffer .= html_encode($v);
            $buffer .= '</label>';
        }
        if (!$this->isRequired() && !isset($values[$this->onEmpty])) {
            $js = "$(this).parent().find('input')[0].checked = true;";
            $buffer .= '<input class="forms-radio-clear-button" type="button" value="" onclick="'.$js.'" />';
        }
        $buffer .= '</div>';
        return $buffer;
    }

}
