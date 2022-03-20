<?php

namespace megabike\forms\fields;

class FloatField extends BaseFormField
{
    protected $maxlength = 255;
    protected $onEmpty = 0;
    protected $allowNegative = true;
    protected $allowZero = true;

    public function validate($formValue, &$error = null)
    {
        if (!preg_match('/^[0-9\-\.]+$/', $formValue) || !is_numeric($formValue)) {
            return false;
        }

        $value = (float)$formValue;
        if (!$this->allowNegative && $value < 0) {
            return false;
        }
        if (!$this->allowZero && !$value) {
            return false;
        }

        return true;
    }

    protected function prepareOutputValue($formValue)
    {
        if ($this->isEmpty($formValue)) {
            return $this->onEmpty;
        } else {
            return (float)$formValue;
        }
    }

}
