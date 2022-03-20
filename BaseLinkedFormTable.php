<?php

namespace megabike\forms;

class BaseLinkedFormTable extends BaseDbFormTable
{
    /**
     *
     * @var FormControllerInterface
     */
    protected $controller;

    /**
     * 
     * @param FormControllerInterface $controller
     */
    public function __construct($controller)
    {
        $this->controller = $controller;
        parent::__construct();
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
        return $this->controller->getFieldsDeclarations();
    }

    protected function getButtonsDeclarations()
    {
        return $this->controller->getButtonsDeclarations();
    }
    
    protected function getFiltersDeclarations()
    {
        return $this->controller->getFiltersDeclarations();
    }

    protected function getNameFieldId()
    {
        return $this->controller->getNameFieldId();
    }

    protected function transformDbData($dbRow)
    {
        return $this->controller->transformDbData($dbRow);
    }

    protected function initializeQuery($manager)
    {
        return $this->controller->initializeDbQuery($manager);
    }

}
