<?php

namespace megabike\forms;

use LogicException;

abstract class AbstractForm extends FieldsManager
{

    protected $scenario = '';
    //
    protected $isDataLoaded = false;
    protected $isDataFound = false;
    protected $dataId = null;
    protected $storedData = null;
    protected $defaultData = null;
    //
    protected $isProcessed = false;
    protected $isValid = false;
    protected $messages = null;
    protected $values = null;
    //
    protected $inputData = null;
    protected $outputData = null;

    /**
     * 
     * @param string $scenario
     * @param mixed $storedDataId
     * @param mixed $inputData
     */
    public function __construct($scenario = '', $storedDataId = null, $inputData = null)
    {
        $this->scenario = (string)$scenario;
        parent::__construct();
        if ($inputData !== null) {
            $this->setInputData($inputData);
        }
        if ($storedDataId !== null) {
            $this->setDataId($storedDataId);
        }
    }

    /**
     * 
     */
    public function reset()
    {
        $this->resetStoredData();
        $this->isProcessed = false;
        $this->isValid = false;
        $this->messages = null;
        $this->values = null;
        $this->outputData = null;
    }

    /**
     * 
     */
    protected function resetStoredData()
    {
        $this->isDataLoaded = false;
        $this->isDataFound = false;
        $this->storedData = null;
    }

    /**
     * 
     * @param string $text
     * @param int $level
     * @param boolean $rawOutput
     * @return array
     */
    protected function createSuccessMessage($text, $level = 0, $rawOutput = false)
    {
        return FormMessages::createSuccess($text, $level, $rawOutput);
    }

    /**
     * 
     * @param string $text
     * @param int $level
     * @param boolean $rawOutput
     * @return array
     */
    protected function createErrorMessage($text, $level = 0, $rawOutput = false)
    {
        return FormMessages::createError($text, $level, $rawOutput);
    }

    /**
     * 
     * @param string $text
     * @param int $level
     * @param boolean $rawOutput
     * @return boolean
     */
    public function setError($text, $level = 2, $rawOutput = false)
    {
        $this->messages[] = $this->createErrorMessage($text, $level, $rawOutput);
        return false;
    }

    /**
     * 
     * @param mixed $message
     * @param string $fieldId
     * @return boolean
     */
    public function setFieldError($message, $fieldId)
    {
        $this->messages[] = $this->buildFieldError($message, $fieldId);
        return false;
    }

    /**
     * 
     * @param string $text
     * @param int $level
     * @param boolean $rawOutput
     * @return boolean
     */
    public function setSuccess($text, $level = 2, $rawOutput = false)
    {
        $this->messages[] = $this->createSuccessMessage($text, $level, $rawOutput);
        return true;
    }

    /**
     * 
     * @return string
     */
    public final function getScenario()
    {
        return $this->scenario;
    }

    /**
     * 
     * @return mixed
     */
    public final function getInputData()
    {
        return $this->inputData;
    }

    /**
     * 
     * @param mixed $inputData
     */
    public final function setInputData($inputData)
    {
        $this->inputData = $inputData !== null ? $this->transformInputData($inputData) : null;
        $this->reset();
    }

    /**
     * 
     * @return mixed
     */
    public final function getDataId()
    {
        return $this->dataId;
    }

    /**
     * 
     * @param mixed $id
     */
    public final function setDataId($id)
    {
        $this->dataId = $id !== null ? $this->transformDataId($id) : null;
        $this->reset();
    }

    /**
     * 
     * @param mixed $inputData
     * @return mixed
     */
    protected function transformInputData($inputData)
    {
        return $inputData;
    }

    /**
     * 
     * @param mixed $id
     * @return mixed
     */
    protected function transformDataId($id)
    {
        return $id;
    }

    /**
     * 
     * @return mixed
     */
    protected abstract function loadStoredData();

    /**
     * 
     * @return mixed
     */
    protected function getDefaultData()
    {
        if ($this->defaultData !== null || $this->defaultData !== false) {
            if ($this->defaultData instanceof FormData) {
                return $this->defaultData;
            } else {
                return new FormData($this->defaultData);
            }
        }
        return null;
    }

    /**
     * 
     * @param mixed $data
     */
    public function setDefaultData($data)
    {
        $this->defaultData = $data;
    }

    /**
     * 
     * @return array
     */
    protected abstract function getAvailableScenarios();

    /**
     * 
     * @return mixed
     */
    public final function getStoredData()
    {
        if (!$this->isDataLoaded) {
            $this->isDataLoaded = true;
            if ($this->dataId !== null) {
                $this->storedData = $this->loadStoredData();
                $this->isDataFound = $this->storedData !== null;
            } else {
                $this->storedData = null;
                $this->isDataFound = false;
            }
        }
        return $this->storedData;
    }

    /**
     * 
     * @return mixed
     */
    public final function getStoredOrDefaultData()
    {
        $data = $this->getStoredData();
        if ($data !== null) {
            return $data;
        } else {
            return $this->getDefaultData();
        }
    }

    /**
     * 
     * @return boolean
     */
    public final function isDataFound()
    {
        return $this->getStoredData() !== null && $this->isDataFound;
    }

    /**
     * 
     * @return boolean
     */
    protected function startCurrentScenario()
    {
        if (in_array($this->scenario, $this->getAvailableScenarios()) && !empty($this->inputData)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 
     * @return mixed
     */
    protected function processCurrentScenario()
    {
        return $this->processDefaultScenario();
    }

    /**
     * 
     * @param array $fields
     * @return array
     */
    protected function orderFieldsByDependency($fields)
    {
        $result = array();
        $queue = array_values($fields);
        $count = count($queue);
        while ($field = array_shift($queue)) {
            if ($field instanceof FormFieldInterface) {
                $dependencies = $field->getFieldDependencies();
                if ($dependencies) {
                    $skip = false;
                    foreach ((array)$dependencies as $id) {
                        if (!isset($result[$id])) {
                            $skip = true;
                            break;
                        }
                    }
                    if ($skip) {
                        $queue[] = $field;
                        $count--;
                        if ($count <= 0) {
                            throw new LogicException("Invalid field dependencies for field '{$field->getId()}'");
                        } else {
                            continue;
                        }
                    }
                }
            }
            $result[$field->getId()] = $field;
            $count = count($queue);
        }
        return $result;
    }

    /**
     * 
     * @param mixed $message
     * @param string $fieldId
     * @return array
     */
    protected function buildFieldError($message, $fieldId)
    {
        if (!is_array($message)) {
            $message = array('message' => (string)$message);
        }
        if (!isset($message['message']) || $message['message'] === '') {
            $message['message'] = 'Unknown error';
        }
        if (!isset($message['fieldId'])) {
            $message['fieldId'] = $fieldId;
        }
        $message['type'] = FormMessages::TYPE_ERROR;
        return $message;
    }

    /**
     * 
     * @param FieldInterface $field
     * @param mixed $inputData
     * @return mixed
     */
    protected function extractInputForField($field, $inputData)
    {
        return $inputData;
    }

    /**
     * 
     * @param FormFieldInterface $field
     * @return string
     */
    public function generateInputName($field)
    {
        return $field->getId();
    }

    /**
     * 
     * @param FormFieldInterface $field
     * @param mixed value
     * @param FormData $storedData
     * @return boolean
     */
    protected function canKeepInvalidValue($field, $value, $storedData)
    {
        if (!$field->isAllowKeep()) {
            return false;
        }
        if (empty($storedData) || ($storedData instanceof FormData && $storedData->isEmpty()) || $field->isEmpty($value)) {
            return false;
        }

        $data = $this->extractDataForField($field, $storedData);
        $oldValue = $field->getNormalValue($data);
        if ($oldValue !== null && $value !== null) {
            return is_scalar($value) && is_scalar($oldValue) && (string)$value === (string)$oldValue;
        }
        return false;
    }

    /**
     * 
     * @param FormFieldInterface $field
     * @param FormData $storedData
     * @param mixed $inputData
     * @return boolean
     */
    protected function processField($field, $storedData, $inputData)
    {
        $error = null;
        $value = null;
        $data = $this->extractDataForField($field, $storedData);
        $input = $this->extractInputForField($field, $inputData);
        if ($field->processInput($data, $input, $value, $error)) {
            $this->values[$field->getId()] = $value;
            return true;
        } elseif ($value !== null && $this->canKeepInvalidValue($field, $value, $storedData)) {
            return true;
        } else {
            $this->messages[] = $this->buildFieldError($error, $field->getId());
            return false;
        }
    }

    /**
     * 
     * @param FormFieldInterface $field
     * @param FormData $storedData
     * @param FormData $outputData
     * @return boolean
     */
    protected function validateFieldOutput($field, $storedData, $outputData)
    {
        $error = null;
        $data = $this->extractDataForField($field, $storedData);
        $output = $this->extractOutputSubset($field, $data, $outputData);
        if ($field->validateOutput($data, $output, $error)) {
            return true;
        } else {
            $this->messages[] = $this->buildFieldError($error, $field->getId());
            return false;
        }
    }

    /**
     * 
     * @param FormFieldInterface $field
     * @param FormData $storedData
     * @param FormData $outputData
     * @return boolean
     */
    protected function writeFieldOutput($field, $storedData, $outputData)
    {
        $data = $this->extractDataForField($field, $storedData);
        $output = $this->extractOutputSubset($field, $data, $outputData);
        $field->writeOutput($data, $this->values, $output);
    }

    /**
     * 
     * @param FieldInterface $field
     * @return boolean
     */
    protected function isFieldReadOnly($field)
    {
        return !($field instanceof FormFieldInterface) || $field->isReadOnly();
    }

    /**
     * 
     * @return array
     */
    protected function getFieldsForCurrentScenario()
    {
        return $this->fields;
    }

    /**
     * 
     * @return array
     */
    protected function getFormFieldsForCurrentScenario()
    {
        $fields = $this->getFieldsForCurrentScenario();
        $result = array();
        foreach ($fields as $field) {
            if (!$this->isFieldReadOnly($field)) {
                $result[$field->getId()] = $field;
            }
        }
        return $result;
    }

    /**
     * 
     * @return mixed
     */
    protected function processDefaultScenario()
    {
        $fields = $this->orderFieldsByDependency($this->getFormFieldsForCurrentScenario());
        $inputData = $this->inputData;
        $storedData = $this->getStoredOrDefaultData();
        $outputData = new FormData();
        $result = true;
        foreach ($fields as $field) {
            $result = $this->processField($field, $storedData, $inputData) && $result;
        }
        if ($result) {
            foreach ($fields as $field) {
                $this->writeFieldOutput($field, $storedData, $outputData);
            }
            foreach ($fields as $field) {
                $result = $this->validateFieldOutput($field, $storedData, $outputData) && $result;
            }
            if ($result) {
                return $outputData;
            }
        }
        return false;
    }

    /**
     * 
     * @return boolean
     */
    public final function isProcessed()
    {
        return $this->isProcessed;
    }

    /**
     * 
     * @return boolean
     */
    public final function isValid()
    {
        if ($this->isProcessed) {
            return $this->isValid;
        } else {
            return $this->process();
        }
    }

    /**
     * 
     * @return boolean
     */
    public function process()
    {
        if ($this->isProcessed) {
            return $this->isValid;
        }

        $this->values = array();
        $this->messages = array();
        $this->isProcessed = true;
        $this->outputData = null;
        if (!$this->startCurrentScenario()) {
            $this->isValid = false;
            return false;
        }

        $output = $this->processCurrentScenario();
        if ($output === false) {
            $this->isValid = false;
            return false;
        }

        if ($this->afterProcess($output)) {
            $this->isValid = true;
            $this->outputData = $output;
            return true;
        } else {
            $this->isValid = false;
            return false;
        }
    }

    /**
     * 
     * @param array $output
     * @return boolean
     */
    protected function afterProcess($output)
    {
        return true;
    }

    /**
     * 
     * @param FormFieldInterface $field
     * @param FormData $dataSubset
     * @param FormData $output
     * @return FormData
     */
    protected function extractOutputSubset($field, $dataSubset, $output)
    {
        if ($output === null) {
            return null;
        }
        if ($dataSubset instanceof FormData && $dataSubset->getSubsetId() !== '') {
            return $output->getSubsetOrCreate($dataSubset->getSubsetId());
        }
        if ($field->getSubset() !== null) {
            return $output->getSubsetOrCreate($field->getSubset());
        }
        return $output;
    }

    /**
     * 
     * @return array
     */
    public final function getOutputData()
    {
        if (!$this->isProcessed) {
            $this->process();
        }
        return $this->outputData;
    }

    /**
     * 
     * @param string $key
     * @return mixed
     */
    public final function getOutputValue($key)
    {
        if (!$this->isProcessed) {
            $this->process();
        }
        return isset($this->outputData[$key]) ? $this->outputData[$key] : null;
    }

    /**
     * 
     * @return array
     */
    public final function getFieldValues()
    {
        if (!$this->isProcessed) {
            $this->process();
        }
        return $this->values;
    }

    /**
     * 
     * @param string $key
     * @return mixed
     */
    public final function getFieldValue($key)
    {
        if (!$this->isProcessed) {
            $this->process();
        }
        return isset($this->values[$key]) ? $this->values[$key] : null;
    }

    /**
     * 
     * @param mixed $message
     * @return string
     */
    public function getMessageHtml($message)
    {
        if (is_array($message)) {
            $html = empty($message['raw']) ? $message['message'] : html_encode($message['message']);
            if (!empty($message['fieldId'])) {
                $field = $this->getField($message['fieldId']);
                $name = $field ? $field->getName() : $message['fieldId'];
                return html_encode($name).": {$html}";
            } else {
                return $html;
            }
        }
        return $message;
    }

    /**
     * 
     * @return array
     */
    public final function getMessages()
    {
        return (array)$this->messages;
    }

    /**
     * 
     * @return array
     */
    public final function getErrors()
    {
        return FormMessages::filter($this->messages, null, FormMessages::TYPE_ERROR);
    }

    /**
     * 
     * @return array
     */
    public function getFieldMessages($fieldId)
    {
        return FormMessages::filter($this->messages, null, null, array('fieldId' => $fieldId));
    }

    /**
     * 
     * @return array
     */
    public function getFieldErrors($fieldId)
    {
        return FormMessages::filter($this->messages, null, FormMessages::TYPE_ERROR, array('fieldId' => $fieldId));
    }

}
