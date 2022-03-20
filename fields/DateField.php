<?php

namespace megabike\forms\fields;

class DateField extends DateTimeField
{

    protected $format = "Y-m-d";
    protected $pattern = "%04d-%02d-%02d";
    protected $timeString = '';

    protected function prepareOutputValue($formValue)
    {
        if ($this->isEmpty($formValue)) {
            return $this->onEmpty;
        } else {
            $y = (int)$formValue[0];
            $m = (int)$formValue[1];
            $d = (int)$formValue[2];
            $date = sprintf($this->pattern, $y, $m, $d, 0, 0, 0);
            if ($date !== '' && (string)$this->timeString !== '') {
                return $date.' '.$this->timeString;
            } else {
                return $date;
            }
        }
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
        $buffer .= '</div>';

        return $buffer;
    }

}
