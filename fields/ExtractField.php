<?php

namespace megabike\forms\fields;

use megabike\forms\FormData;
use megabike\forms\FormHelper;

class ExtractField extends StringField
{

    protected $pattern = null;
    protected $extractor = null;
    protected $call = null;
    protected $map = null;

    public function isReadOnly()
    {
        return parent::isReadOnly() || $this->field === null;
    }

    /**
     * 
     * @param FormData $object
     * @return FormData
     */
    protected function formData($object)
    {
        $data = $object instanceof FormData ? $object : new FormData($object, $this->extractor);
        $subset = $this->getSubset();
        if ($subset !== null) {
            return $data->getSubset($subset);
        }
        return $data;
    }

    public function extractValue($object)
    {
        if ($this->call !== null) {
            $fd = $this->formData($object);
            $model = $fd->getModel();
            if ($model && $model->hasMethod($this->call) && is_callable([$model, $this->call])) {
                $value = call_user_func([$model, $this->call]);
                if ($this->map !== null && isset($this->map[$value])) {
                    return (string)$this->map[$value];
                }
                return (string)$value;
            }
        }
        return null;
    }

    public function getTextValue($object)
    {
        $text = $this->extractValue($object);
        if ($text !== null) {
            return $text;
        }
        if ($this->pattern !== null) {
            $text = FormHelper::processPattern($this->text, $object);
            if ((string)$text !== '') {
                return $text;
            }
        }
        return parent::getTextValue($object);
    }

}
