<?php

namespace megabike\forms\fields;

class ValueBoxField extends VariantsField
{

    protected function transformInputValue($value)
    {
        return (string)$value !== '' ? (int)$value : null;
    }

    protected function prepareOutputValue($formValue)
    {
        if ($this->isEmpty($formValue)) {
            return $this->onEmpty;
        } elseif (isset($this->values[$formValue])) {
            return (int)$formValue;
        } else {
            return $this->onUnknown;
        }
    }

}
