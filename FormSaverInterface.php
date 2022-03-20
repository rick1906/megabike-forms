<?php

namespace megabike\forms;

interface FormSaverInterface
{
    
    public function doSave($form, $scenario, $newData, $id, $oldData);
}
