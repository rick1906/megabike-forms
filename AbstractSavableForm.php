<?php

namespace megabike\forms;

use LogicException;

abstract class AbstractSavableForm extends AbstractForm
{

    const SCENARIO_ADD = 'add';
    const SCENARIO_EDIT = 'edit';
    const SCENARIO_DELETE = 'delete';
    const SCENARIO_VIEW = 'view';

    /**
     * 
     * @param FormData $outputData
     * @param FormData $oldStoredData
     * @param mixed $oldId
     */
    protected function processAfterSave($outputData, $oldStoredData, $oldId)
    {
        if ($this->scenario === self::SCENARIO_DELETE) {
            $this->processFieldsAfterSave($outputData, $oldStoredData, $oldId);
            $this->afterSave(null, $oldStoredData, $oldId);
        } else {
            $this->processFieldsAfterSave($outputData, $oldStoredData, $oldId);
            $this->afterSave($outputData, $oldStoredData, $oldId);
        }
    }

    /**
     * 
     * @param FormData $outputData
     * @param FormData $oldStoredData
     * @param mixed $oldId
     * @return boolean
     */
    protected function processFieldsAfterSave($outputData, $oldStoredData, $oldId)
    {
        $result = true;
        $fields = $this->orderFieldsByDependency($this->getFormFieldsForCurrentScenario());
        foreach ($fields as $field) {
            $result = $this->processFieldAfterSave($field, $outputData, $oldStoredData, $oldId) && $result;
        }
        return $result;
    }

    /**
     * 
     * @param FormFieldInterface $field
     * @param FormData $outputData
     * @param FormData $oldStoredData
     * @param mixed $oldId
     * @return boolean
     */
    protected function processFieldAfterSave($field, $outputData, $oldStoredData, $oldId)
    {
        $error = null;
        $oldData = $this->extractDataForField($field, $oldStoredData);
        if ($this->scenario === self::SCENARIO_DELETE) {
            $result = $field->afterSave(null, null, $oldData, $oldId, $error);
        } else {
            $output = $this->extractOutputSubset($field, $oldData, $outputData);
            $result = $field->afterSave($output, $this->dataId, $oldData, $oldId, $error);
        }
        if (!$result) {
            $this->messages[] = $this->buildFieldError($error, $field->getId());
        }
        return true;
    }

    /**
     * 
     */
    protected function resetAfterSave()
    {
        $this->resetStoredData();
    }

    /**
     * 
     * @param array $data
     * @return mixed
     */
    protected abstract function saveToStorage($data);

    /**
     * 
     * @return boolean
     */
    public function save()
    {
        if (!$this->isProcessed) {
            throw new LogicException("Form needs to be processed before saving");
        }
        if ($this->beforeSave($this->outputData)) {
            $oldStoredData = $this->getStoredData();
            $oldId = $this->dataId;
            $id = $this->saveToStorage($this->outputData);
            if ($id !== false) {
                $this->resetAfterSave();
                $this->dataId = $id !== true ? $id : $oldId;
                $this->processAfterSave($this->outputData, $oldStoredData, $oldId);
                return true;
            }
        }
        return false;
    }

    /**
     * 
     * @return array
     */
    protected function getAvailableScenarios()
    {
        return array(self::SCENARIO_ADD, self::SCENARIO_EDIT, self::SCENARIO_DELETE, self::SCENARIO_VIEW);
    }

    /**
     * 
     * @return array
     */
    protected function getFieldsForCurrentScenario()
    {
        if ($this->scenario === self::SCENARIO_DELETE || $this->scenario === self::SCENARIO_VIEW) {
            return array();
        } else {
            return $this->fields;
        }
    }

    /**
     * 
     * @param FieldInterface $field
     * @return boolean
     */
    protected function isFieldReadOnly($field)
    {
        if ($this->scenario === self::SCENARIO_DELETE || $this->scenario === self::SCENARIO_VIEW) {
            return true;
        } else {
            return parent::isFieldReadOnly($field);
        }
    }

    /**
     * 
     * @param FormData $outputData
     * @return boolean
     */
    protected function beforeSave($outputData)
    {
        if ($this->scenario === self::SCENARIO_ADD) {
            return $this->beforeAdd($outputData);
        }
        if ($this->scenario === self::SCENARIO_EDIT) {
            return $this->beforeEdit($outputData);
        }
        if ($this->scenario === self::SCENARIO_DELETE) {
            return $this->beforeDelete();
        }
        return true;
    }

    /**
     * 
     * @param FormData $outputData
     * @return boolean
     */
    protected function beforeAdd($outputData)
    {
        $defaults = $this->getDefaultData();
        if ($defaults !== null) {
            foreach ($defaults as $key => $value) {
                if (!$outputData->keyExists($key)) {
                    $outputData[$key] = $value;
                }
            }
        }
        return true;
    }

    /**
     * 
     * @param FormData $outputData
     * @return boolean
     */
    protected function beforeEdit($outputData)
    {
        return true;
    }

    /**
     * 
     * @return boolean
     */
    protected function beforeDelete()
    {
        return true;
    }

    /**
     * 
     * @param FormData $outputData
     * @param FormData $oldStoredData
     * @param mixed $oldId
     */
    protected function afterSave($outputData, $oldStoredData, $oldId)
    {
        if ($this->scenario === self::SCENARIO_ADD) {
            return $this->afterAdd($outputData, $oldStoredData);
        }
        if ($this->scenario === self::SCENARIO_EDIT) {
            return $this->afterEdit($outputData, $oldStoredData);
        }
        if ($this->scenario === self::SCENARIO_DELETE) {
            return $this->afterDelete($oldStoredData, $oldId);
        }
    }

    /**
     * 
     * @param FormData $outputData
     * @param FormData $oldStoredData
     */
    protected function afterAdd($outputData, $oldStoredData)
    {
        
    }

    /**
     * 
     * @param FormData $outputData
     * @param FormData $oldStoredData
     */
    protected function afterEdit($outputData, $oldStoredData)
    {
        
    }

    /**
     * 
     * @param FormData $oldStoredData
     * @param mixed $oldId
     */
    protected function afterDelete($oldStoredData, $oldId)
    {
        
    }

}
