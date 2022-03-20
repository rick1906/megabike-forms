<?php

namespace megabike\forms\fields;

use megabike\forms\FormFieldInterface;

abstract class AbstractFormField extends AbstractField implements FormFieldInterface
{

    public function isReadOnly()
    {
        return $this->getAttribute('readOnly', $this->getAttribute('readonly', false));
    }

    public function isRequired()
    {
        return $this->getAttribute('required');
    }

    public function isAllowKeep()
    {
        return $this->getAttribute('allowKeep', true);
    }

    public function getFieldDependencies()
    {
        return $this->getAttribute('dependencies');
    }

    public function isWhereField()
    {
        return $this->isRealField() && $this->getAttribute('isWhere', true);
    }

    public function isOrderField()
    {
        return parent::isOrderField() || $this->isRealField() && $this->getAttribute('isOrder', true);
    }

    public abstract function getFormValue($object = null, $input = null);

    public abstract function generateInputHtml($parent, $object = null, $input = null);

    public abstract function isEmpty($formValue);

    public abstract function validate($formValue, &$error = null);

    protected abstract function generateDefaultError();

    protected abstract function generateRequiredError();

    protected abstract function writeOutputValue($formValue, $object, &$output);

    public function processInput($object, $input, &$value, &$error = null)
    {
        $value = $this->getFormValue($object, $input);
        if ($this->isEmpty($value)) {
            if ($this->isRequired()) {
                $error = $this->generateRequiredError();
                return false;
            } else {
                return true;
            }
        }

        $error = null;
        if (!$this->validate($value, $error)) {
            $error = $error ? $error : $this->generateDefaultError();
            return false;
        }

        return true;
    }

    public function writeOutput($object, $values, &$output)
    {
        if (array_key_exists($this->id, $values)) {
            $this->writeOutputValue($values[$this->id], $object, $output);
            return true;
        }
        return false;
    }

    public function validateOutput($object, $output, &$error = null)
    {
        return true;
    }

    public function afterSave($output, $savedId, $oldData, $oldId, &$error = null)
    {
        return true;
    }

}
