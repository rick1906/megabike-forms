<?php

namespace megabike\forms;

interface CriteriaManagerInterface
{

    public function setIsNavigationEnabled($flag);

    public function isNavigationEnabled();

    public function setIsFilterEnabled($flag);

    public function isFilterEnabled();

    public function setIsSortingEnabled($flag);

    public function isSortingEnabled();

    public function setStartIndex($index);

    public function getStartIndex();

    public function setOnPage($onPage);

    public function getOnPage();

    public function setCustomOnPage($onPage);

    public function setPage($page);

    public function getPage();

    public function setKeepOrderAmount($value);

    public function getKeepOrderAmount();

    public function setNearbyPagesAmount($value);

    public function getNearbyPagesAmount();

    public function getPageByIndex($index);

    public function getTotalRecords();

    public function getTotalPages();

    public function processWhereInput($input);

    public function getWhereFilter($short = false);

    public function setWhereFilter($filter, $replace = true);

    public function setWhereFilterDefaults($filter, $replace = true);

    public function processOrderInput($input);

    public function getOrderFilter($short = false);

    public function setOrderFilter($filter, $replace = true);

    public function setOrderFilterDefaults($filter, $replace = true);

    public function setFilterInput($input);

    public function getFilterParams($short = false, $nulls = false);

    public function setBaseUrl($baseUrl);

    public function getBaseUrl();
    
    public function getCurrentUrl();

    public function generateUrl($baseUrl, $keepQuery = true, $params = array(), $makeShort = true);

    public function generateResetUrl($baseUrl, $keepQuery = true);

    public function generateCurrentUrl($baseUrl, $keepQuery = true);

    public function getFieldOrderStatus($fieldId, $onlyPrimary = true);

    public function generateOrderSwitchUrl($baseUrl, $fieldId);

    public function generateNavigation($baseUrl, $pagesToShow = null, $pageKey = null);

    public function generateShortNavigation($baseUrl, $pagesToShow = null, $pageKey = null);
}
