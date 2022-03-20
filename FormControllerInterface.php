<?php

namespace megabike\forms;

interface FormControllerInterface
{

    public function getDbTable();

    public function getDbTableKey();

    public function getDbTableAlias();

    public function getFieldsDeclarations($scenario = null);

    public function getButtonsDeclarations();

    public function getFiltersDeclarations();

    public function getNameFieldId();

    public function transformDbData($dbRow, $scenario = null);

    public function initializeDbQuery($manager, $scenario = null);
}
