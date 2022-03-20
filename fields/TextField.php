<?php

namespace megabike\forms\fields;

class TextField extends StringField
{

    protected $style = '';
    protected $maxlength = null;
    protected $onEmpty = null;

    public function getHtmlValue($object)
    {
        return nl2br(html_encode($this->getTextValue($object)));
    }

    public function generateInputHtml($parent, $object = null, $input = null)
    {
        if ($object !== null || $input !== null) {
            $value = $this->getFormValue($object, $input);
        } else {
            $value = null;
        }

        $name = $this->generateInputName($parent);

        $style = trim($this->style, '; ');

        $buffer = '';
        $buffer .= '<textarea name="'.$name.'" style="'.html_encode($style).'">';
        $buffer .= html_encode($value);
        $buffer .= '</textarea>';
        return $buffer;
    }

}
