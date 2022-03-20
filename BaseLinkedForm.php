<?php

namespace megabike\forms;

class BaseLinkedForm extends BaseDbForm
{

    /**
     *
     * @var FormControllerInterface
     */
    protected $controller;

    /**
     * 
     * @param FormControllerInterface $controller
     * @param string $scenario
     * @param mixed $storedDataId
     * @param mixed $inputData
     */
    public function __construct($controller, $scenario = '', $storedDataId = null, $inputData = null)
    {
        $this->controller = $controller;
        parent::__construct($scenario, $storedDataId, $inputData);
    }
    
    public function getController()
    {
        return $this->controller;
    }

    public function getTable()
    {
        return $this->controller->getDbTable();
    }

    public function getTableKey()
    {
        return $this->controller->getDbTableKey();
    }

    public function getTableAlias()
    {
        return $this->controller->getDbTableAlias();
    }

    protected function getFieldsDeclarations()
    {
        return $this->controller->getFieldsDeclarations($this->scenario);
    }

    protected function getNameFieldId()
    {
        return $this->controller->getNameFieldId();
    }

    protected function transformDbData($dbRow)
    {
        return $this->controller->transformDbData($dbRow, $this->scenario);
    }

    protected function initializeQuery($manager)
    {
        return $this->controller->initializeDbQuery($manager, $this->scenario);
    }
    
    protected function afterProcess($output)
    {
        if (!parent::afterProcess($output)) {
            return false;
        }
        if ($this->controller instanceof FormListenerInterface) {
            $r = $this->controller->afterProcess($this, $this->scenario, $output, $this->dataId, $this->getStoredData());
            if ($r !== null) {
                return $r;
            }
        }
        return true;
    }
    
    protected function beforeSave($outputData)
    {
        if (!parent::beforeSave($outputData)) {
            return false;
        }
        if ($this->controller instanceof FormListenerInterface) {
            $r = $this->controller->beforeSave($this, $this->scenario, $outputData, $this->dataId, $this->getStoredData());
            if ($r !== null) {
                return $r;
            }
        }
        return true;
    }

    protected function afterSave($outputData, $oldStoredData, $oldId)
    {
        parent::afterSave($outputData, $oldStoredData, $oldId);
        if ($this->controller instanceof FormListenerInterface) {
            $dataId = $outputData !== null ? $this->dataId : null;
            $this->controller->afterSave($this, $this->scenario, $outputData, $dataId, $oldStoredData, $oldId);
        }
    }

    protected function saveToStorage($data)
    {
        if ($this->controller instanceof FormSaverInterface) {
            $r = $this->controller->doSave($this, $this->scenario, $data, $this->dataId, $this->getStoredData());
            if ($r !== null) {
                return $r;
            }
        }
        return parent::saveToStorage($data);
    }

}
