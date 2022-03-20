<?php

namespace megabike\forms\fields;

class DateStringField extends StringField
{
    protected $withYear = false;

    protected function dateToText($value)
    {
        $dateArray = $this->dateToArray($value);
        if (empty($dateArray)) {
            return '';
        } elseif ($this->withYear && !empty($dateArray[0])) {
            return sprintf('%02d.%02d.%04d', $dateArray[2], $dateArray[1], $dateArray[0]);
        } else {
            return sprintf('%02d.%02d', $dateArray[2], $dateArray[1]);
        }
    }
    
    protected function dateToNormalValue($value)
    {
        $dateArray = $this->dateToArray($value);
        if (empty($dateArray)) {
            return '';
        } elseif ($this->withYear && !empty($dateArray[0])) {
            return sprintf('%04d%02d%02d', $dateArray[0], $dateArray[1], $dateArray[2]);
        } else {
            return sprintf('%02d%02d', $dateArray[1], $dateArray[2]);
        }
    }

    protected function dateToArray($value)
    {
        if (empty($value)) {
            return null;
        }
        if (preg_match('/^\d+$/', $value)) {
            $len = strlen($value);
            if ($len === 8) {
                return array((int)substr($value, 0, 4), (int)substr($value, 4, 2), (int)substr($value, 6, 2));
            } elseif ($len == 4) {
                return array(null, (int)substr($value, 0, 2), (int)substr($value, 2, 2));
            } else {
                return false;
            }
        } else {
            $parts = preg_split('#[\./]#', trim($value));
            foreach ($parts as $part) {
                if (!is_numeric($part)) {
                    return false;
                }
            }
            if (count($parts) == 2) {
                return array(null, (int)$parts[1], (int)$parts[0]);
            }
            if (count($parts) == 3) {
                return array((int)$parts[2], (int)$parts[1], (int)$parts[0]);
            }
            return false;
        }
    }

    public function getTextValue($object)
    {
        return $this->dateToText($this->getNormalValue($object));
    }

    protected function transformNormalValue($value)
    {
        return $this->dateToText($value);
    }

    protected function prepareOutputValue($formValue)
    {
        if ($this->isEmpty($formValue)) {
            return $this->onEmpty;
        } else {
            return $this->dateToNormalValue($formValue);
        }
    }

    public function validate($formValue, &$error = null)
    {
        if ($this->isEmpty($formValue)) {
            return true;
        }

        $dateArray = $this->dateToArray($formValue);
        if (empty($dateArray)) {
            return false;
        }
        if (empty($dateArray[0])) {
            $time = mktime(0, 0, 0, (int)$dateArray[1], (int)$dateArray[2], 2000);
        } else {
            $time = mktime(0, 0, 0, (int)$dateArray[1], (int)$dateArray[2], (int)$dateArray[0]);
        }
        if (!$time) {
            return false;
        }

        $d = (int)date('d', $time);
        $m = (int)date('m', $time);
        $y = (int)date('Y', $time);
        if ($d !== (int)$dateArray[2] || $m !== (int)$dateArray[1]) {
            return false;
        }
        if (!empty($dateArray[0]) && $y !== (int)$dateArray[0]) {
            return false;
        }
        return true;
    }

}
