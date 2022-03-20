<?php

/*
 * Copyright (c) 2021 Rick
 *
 * This file is a part of a project 'megabike'.
 * MIT licence.
 */

namespace megabike\forms;

use BadMethodCallException;

/**
 *
 * @author Rick
 */
class FormController implements FormControllerInterface, FormListenerInterface, FormSaverInterface
{

    /**
     * 
     * @var FormControllerInterface
     */
    protected $parentController;
    //
    public $dbTable = null;
    public $dbTableKey = null;
    public $dbTableAlias = null;
    public $nameFieldId = null;
    public $fieldsDeclarations = [];
    public $filtersDeclarations = [];
    public $buttonsDeclarations = [];
    public $keepParentQuery = true;
    public $dataExtractor = null;
    //
    public $doSave = null;
    public $beforeSave = null;
    public $afterSave = null;
    public $afterProcess = null;
    public $transformDbData = null;
    public $initializeDbQuery = null;

    public function __construct($parent = null)
    {
        $this->parentController = $parent;
    }

    public function afterSave($form, $scenario, $newData, $newId, $oldData, $oldId)
    {
        if ($this->afterSave !== null) {
            return call_user_func($this->afterSave, $form, $scenario, $newData, $newId, $oldData, $oldId);
        }
    }

    public function beforeSave($form, $scenario, $newData, $id, $oldData)
    {
        if ($this->beforeSave !== null) {
            return call_user_func($this->beforeSave, $form, $scenario, $newData, $id, $oldData);
        }
        return true;
    }

    public function doSave($form, $scenario, $newData, $id, $oldData)
    {
        if ($this->doSave !== null) {
            return call_user_func($this->doSave, $form, $scenario, $newData, $id, $oldData);
        }
        return null;
    }

    public function afterProcess($form, $scenario, $newData, $id, $oldData)
    {
        if ($this->afterProcess !== null) {
            return call_user_func($this->afterProcess, $form, $scenario, $newData, $id, $oldData);
        }
        return true;
    }

    public function getButtonsDeclarations()
    {
        return $this->buttonsDeclarations;
    }

    public function getDbTable()
    {
        if ($this->dbTable !== null) {
            return $this->dbTable;
        } elseif ($this->parentController !== null) {
            return $this->parentController->getDbTable();
        } else {
            throw new BadMethodCallException("Not implemented");
        }
    }

    public function getDbTableAlias()
    {
        if ($this->dbTableAlias !== null) {
            return $this->dbTableAlias;
        } elseif ($this->parentController !== null) {
            return $this->parentController->getDbTableAlias();
        } else {
            throw new BadMethodCallException("Not implemented");
        }
    }

    public function getDbTableKey()
    {
        if ($this->dbTableKey !== null) {
            return $this->dbTableKey;
        } elseif ($this->parentController !== null) {
            return $this->parentController->getDbTableKey();
        } else {
            throw new BadMethodCallException("Not implemented");
        }
    }

    public function getFieldsDeclarations($scenario = null)
    {
        return $this->fieldsDeclarations;
    }

    public function getFiltersDeclarations()
    {
        return $this->filtersDeclarations;
    }

    public function getNameFieldId()
    {
        if ($this->nameFieldId !== null) {
            return $this->nameFieldId;
        }
        if ($this->parentController !== null) {
            return $this->parentController->getNameFieldId();
        }
        throw new BadMethodCallException("Not implemented");
    }

    public function initializeDbQuery($manager, $scenario = null)
    {
        if ($this->parentController !== null && $this->keepParentQuery) {
            $this->parentController->initializeDbQuery($manager, $scenario);
        }
        if ($this->initializeDbQuery !== null) {
            call_user_func($this->initializeDbQuery, $manager, $scenario);
        }
    }

    public function transformDbData($dbRow, $scenario = null)
    {
        if ($this->parentController !== null) {
            return $this->parentController->transformDbData($dbRow, $scenario);
        }
        if ($this->transformDbData !== null) {
            return call_user_func($this->transformDbData, $dbRow, $scenario);
        }
        return $dbRow instanceof FormData ? $dbRow : new FormData($dbRow, $this->dataExtractor);
    }

}
