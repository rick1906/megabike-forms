<?php

namespace megabike\forms\fields;

class CheckBoxField extends ValueBoxField
{
    protected $onEmpty = 0;
    protected $onUnknown = 0;
    protected $display = 'radio';

    protected function initialize()
    {
        parent::initialize();
        if (!isset($this->values[0])) {
            $this->values[0] = tl('Нет');
        }
        if (!isset($this->values[1])) {
            $this->values[1] = tl('Да');
        }
    }

    protected function transformInputValue($value)
    {
        return $value ? 1 : 0;
    }

}
