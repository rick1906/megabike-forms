<?php

namespace megabike\forms;

interface FormListenerInterface
{

    public function afterSave($form, $scenario, $newData, $newId, $oldData, $oldId);

    public function beforeSave($form, $scenario, $newData, $id, $oldData);

    public function afterProcess($form, $scenario, $newData, $id, $oldData);
}
