<?php

namespace megabike\forms\fields;

class TitleField extends ExtractField
{

    public function extractValue($object)
    {
        if ($this->call === null) {
            $fd = $this->formData($object);
            $text = $fd->getName();
            if ($text !== null) {
                return $text;
            }
        }
        return parent::extractValue($object);
    }

}
