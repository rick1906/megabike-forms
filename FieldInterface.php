<?php

namespace megabike\forms;

use ArrayAccess;

interface FieldInterface extends ArrayAccess
{

    public function hasAttribute($name);

    public function getAttribute($name, $defaultValue = null);

    public function setAttribute($name, $value);

    public function removeAttribute($name);

    public function getId();

    public function getSubset();

    public function getName();

    public function getShortName();
    
    public function isRealField();

    public function isOrderField();

    public function isWhereField();

    public function isShow();

    public function isShowFull();

    public function isShowInForm();

    public function getNote();

    public function getExtendedComment();

    public function getTextValue($object);

    public function getNormalValue($object);

    public function getHtmlValue($object);

    public function getFullHtmlValue($object);

    public function getWhereOperators();
    
    public function getFieldExpression($tableAlias = '');

    public function getWhereCondition($value, $operator, $tableAlias = '');

    public function getOrderExpression($order, $tableAlias = '');

    public function getOrderDefaultDirection();
}