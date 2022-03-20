<?php

namespace megabike\forms\fields;

class HtmlField extends TextField
{

    protected $textClass = null;
    protected $wysiwyg = true;
    protected $autosize = false;
    protected $isLargeText = false;
    protected $maxTextLength = null;

    public function isOrderField()
    {
        return false;
    }

    public function generateInputHtml($parent, $object = null, $input = null)
    {
        if ($object !== null || $input !== null) {
            $value = $this->getFormValue($object, $input);
        } else {
            $value = null;
        }

        $style = trim($this->style, ';').';';
        if ($style === ';') {
            $style = '';
        }

        $name = $this->generateInputName($parent);
        $id = $this->generateHtmlId();

        $p = array();
        if (!$this->wysiwyg) {
            $p[] = 'tinymce: false';
        }
        if ($this->autosize) {
            $p[] = 'autosize: true';
        }
        if ($this->isLargeText) {
            $p[] = 'largeText: true';
        }

        $buffer = '';
        $buffer .= '<div id="'.$id.'_container">';
        $buffer .= '<textarea id="'.$id.'" style="'.$style.'" name="'.$name.'">';
        $buffer .= html_encode($value);
        $buffer .= '</textarea>';
        $buffer .= '</div>';

        $buffer .= "\n".'<script type="text/javascript">'."\n";
        $buffer .= "var params = {"."\n";
        $buffer .= implode(",\n", $p)."\n";
        $buffer .= "};"."\n";
        $buffer .= "initHtmlEditor('#{$id}', params);"."\n";
        $buffer .= '</script>'."\n";

        return $buffer;
    }

}
