<?php

namespace megabike\forms\fields;

class StringField extends BaseFormField
{
    protected $maxlength = 255;
    protected $onEmpty = '';

    public function validate($formValue, &$error = null)
    {
        if ($this->maxlength && mb_strlen($formValue) > $this->maxlength) {
            $error = tlp('Длина строки не должна превышать %s символов', $this->maxlength);
            return false;
        }
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

}
