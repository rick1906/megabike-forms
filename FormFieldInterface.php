<?php

namespace megabike\forms;

interface FormFieldInterface extends FieldInterface
{

    public function getFieldDependencies();

    public function isReadOnly();

    public function isRequired();
    
    public function isAllowKeep();

    public function isEmpty($formValue);

    public function validate($formValue, &$error = null);

    public function getFormValue($object = null, $input = null);

    public function processInput($object, $input, &$value, &$error = null);

    public function writeOutput($object, $values, &$output);

    public function generateInputHtml($parent, $object = null, $input = null);

    public function validateOutput($object, $output, &$error = null);

    public function afterSave($output, $savedId, $oldData, $oldId, &$error = null);
}
