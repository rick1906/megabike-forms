<?php

namespace megabike\forms\fields;

class DateTimeField extends StringField
{

    protected $onEmpty = null;
    protected $format = "Y-m-d H:i:s";
    protected $pattern = "%04d-%02d-%02d %02d:%02d:%02d";
    protected $stringInput = false;

    protected function initialize()
    {
        parent::initialize();
        if (!$this->hasAttribute('orderDirection')) {
            $this->setAttribute('orderDirection', 'desc');
        }
    }

    protected function getDateArray($value)
    {
        $date = array('', '', '', '', '', '');
        if (is_array($value)) {
            if (isset($value['y'])) {
                $date[0] = (string)$value['y'];
            }
            if (isset($value['m'])) {
                $date[1] = (string)$value['m'];
            }
            if (isset($value['d'])) {
                $date[2] = (string)$value['d'];
            }
            if (isset($value['h'])) {
                $date[3] = (string)$value['h'];
            }
            if (isset($value['i'])) {
                $date[4] = (string)$value['i'];
            }
            if (isset($value['s'])) {
                $date[5] = (string)$value['s'];
            }
        } else {
            $v = explode(" ", $value, 2);
            if (isset($v[0])) {
                $d1 = explode("-", $v[0]);
                if (count($d1) == 3) {
                    $date[0] = $d1[0];
                    $date[1] = $d1[1];
                    $date[2] = $d1[2];
                } else {
                    $d1 = explode(".", $v[0]);
                    if (count($d1) == 3) {
                        $date[0] = $d1[2];
                        $date[1] = $d1[1];
                        $date[2] = $d1[0];
                    }
                }
            }
            if (isset($v[1])) {
                $d2 = explode(":", $v[1]);
                if (count($d2) == 3) {
                    $date[3] = $d2[0];
                    $date[4] = $d2[1];
                    $date[5] = $d2[2];
                }
            }
        }
        return $date;
    }

    protected function transformInputValue($value)
    {
        return $this->getDateArray($value);
    }

    protected function transformNormalValue($value)
    {
        return $this->getDateArray($value);
    }

    protected function transformToInteger($dateString)
    {
        if ($dateString !== null) {
            $time = strtotime($dateString);
            return $time !== false ? $time : null;
        }
        return null;
    }

    protected function getDateString($value, $useFormat = false)
    {
        if (is_array($value)) {
            if ($this->isEmpty($value)) {
                return $this->onEmpty;
            } else {
                $y = (int)$value[0];
                $m = (int)$value[1];
                $d = (int)$value[2];
                $h = (int)$value[3];
                $i = (int)$value[4];
                $s = (int)$value[5];
                if ($useFormat) {
                    $time = mktime($h, $i, $s, $m, $d, $y);
                    return date($this->format, $time);
                } else {
                    return sprintf($this->pattern, $y, $m, $d, $h, $i, $s);
                }
            }
        }
        return $value;
    }

    public function getNormalValue($object)
    {
        if ($this->field !== null) {
            if (isset($object[$this->field]) && substr($object[$this->field], 0, 7) !== '0000-00') {
                return (string)$object[$this->field];
            } else {
                return null;
            }
        }
        return null;
    }

    public function getTextValue($object)
    {
        $time = $this->transformToInteger($this->getNormalValue($object));
        return $time !== null ? date($this->format, $time) : '';
    }

    public function getHtmlValue($object)
    {
        return nl2br($this->getTextValue($object));
    }

    public function isEmpty($formValue)
    {
        if (empty($formValue)) {
            return true;
        }
        foreach ($formValue as $d) {
            if ($d !== '' && trim($d, '0') !== '') {
                return false;
            }
        }
        return true;
    }

    public function validate($formValue, &$error = null)
    {
        for ($j = 0; $j < 3; ++$j) {
            if (!preg_match('/^\d+$/', $formValue[$j])) {
                return false;
            }
        }
        for ($j = 3; $j < 6; ++$j) {
            if (!preg_match('/^\d*$/', $formValue[$j])) {
                return false;
            }
        }

        $y = (int)$formValue[0];
        $m = (int)$formValue[1];
        $d = (int)$formValue[2];
        $h = (int)$formValue[3];
        $i = (int)$formValue[4];
        $s = (int)$formValue[5];

        $time = mktime($h, $i, $s, $m, $d, $y);
        $date1 = date("Y-m-d H:i:s", $time);
        $date2 = sprintf("%04d-%02d-%02d %02d:%02d:%02d", $y, $m, $d, $h, $i, $s);
        return $date1 === $date2;
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
            return sprintf($this->pattern, $y, $m, $d, $h, $i, $s);
        }
    }

    public function generateInputHtmlOneInput($parent, $object = null, $input = null)
    {
        if ($object !== null || $input !== null) {
            $value = $this->getFormValue($object, $input);
        } else {
            $value = null;
        }

        if ($value !== null) {
            $value = $this->getDateString($value, true);
        }

        $name = $this->generateInputName($parent);

        $buffer = '';
        $buffer .= '<input type="text" name="'.$name.'"';
        $buffer .= ' value="'.html_encode($value).'" />';
        return $buffer;
    }

    public function generateInputHtmlDateInputs($parent, $object = null, $input = null)
    {
        if ($object !== null || $input !== null) {
            $value = $this->getFormValue($object, $input);
        } else {
            $value = null;
        }

        if (!is_array($value)) {
            $value = array('', '', '', '', '', '');
        }

        $name = $this->generateInputName($parent);

        $buffer = '';
        $buffer .= '<div class="date-input">';
        $buffer .= '<span class="date-y">'.'<input type="text" maxlength="4" name="'.$name.'[y]" value="'.html_encode($value[0]).'" title="'.tl('Год').'" />'.'</span>';
        $buffer .= '<span>-</span>';
        $buffer .= '<span class="date-m">'.'<input type="text" maxlength="2" name="'.$name.'[m]" value="'.html_encode($value[1]).'" title="'.tl('Месяц').'" />'.'</span>';
        $buffer .= '<span>-</span>';
        $buffer .= '<span class="date-d">'.'<input type="text" maxlength="2" name="'.$name.'[d]" value="'.html_encode($value[2]).'" title="'.tl('День').'" />'.'</span>';
        $buffer .= '<span>&nbsp;</span>';
        $buffer .= '<span class="date-h">'.'<input type="text" maxlength="2" name="'.$name.'[h]" value="'.html_encode($value[3]).'" title="'.tl('Час').'" />'.'</span>';
        $buffer .= '<span>:</span>';
        $buffer .= '<span class="date-i">'.'<input type="text" maxlength="2" name="'.$name.'[i]" value="'.html_encode($value[4]).'" title="'.tl('Минута').'" />'.'</span>';
        $buffer .= '<span>:</span>';
        $buffer .= '<span class="date-s">'.'<input type="text" maxlength="2" name="'.$name.'[s]" value="'.html_encode($value[5]).'" title="'.tl('Секунда').'" />'.'</span>';
        $buffer .= '</div>';

        return $buffer;
    }

    public function generateInputHtml($parent, $object = null, $input = null)
    {
        if ($this->stringInput) {
            return $this->generateInputHtmlOneInput($parent, $object, $input);
        } else {
            return $this->generateInputHtmlDateInputs($parent, $object, $input);
        }
    }

}
