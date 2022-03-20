<?php

namespace megabike\forms\fields;

class DateTimeIntField extends DateTimeField
{

    protected function getDateArray($value)
    {
        if (!is_array($value) && preg_match('/^\d+$/', $value)) {
            $value = date("Y-m-d H:i:s", (int)$value);
        }
        return parent::getDateArray($value);
    }

    protected function prepareOutputValue($formValue)
    {
        if ($this->isEmpty($formValue)) {
            return $this->onEmpty;
        } else {
            $y = (int)$formValue[0];
            $m = (int)$formValue[1];
            $d = (int)$formValue[2];
            $h = (int)$formValue[3];
            $i = (int)$formValue[4];
            $s = (int)$formValue[5];
            $time = mktime($h, $i, $s, $m, $d, $y);
            return $time;
        }
    }

    public function getNormalValue($object)
    {
        if ($this->field !== null) {
            if (!empty($object[$this->field])) {
                return date("Y-m-d H:i:s", (int)$object[$this->field]);
            } else {
                return null;
            }
        }
        return null;
    }

}
