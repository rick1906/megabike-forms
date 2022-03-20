<?php

namespace megabike\forms;

abstract class AbstractFormTable extends FieldsManager implements FormTableInterface
{

    /**
     *
     * @var CriteriaManagerInterface
     */
    protected $criteriaManager;

    /**
     *
     * @var FormTableFilter
     */
    protected $filterManager;

    /**
     *
     * @var array
     */
    protected $buttons;

    /**
     *
     * @var boolean
     */
    protected $isControlEnabled = true;

    /**
     * 
     */
    protected function initialize()
    {
        parent::initialize();
        $this->criteriaManager = $this->constructCriteriaManager();
        $this->filterManager = $this->constructFilterManager();
        $this->buttons = $this->constructButtons();
    }

    /**
     * 
     * @return CriteriaManagerInterface
     */
    protected abstract function constructCriteriaManager();

    /**
     * 
     * @return FormTableFilter
     */
    protected function constructFilterManager()
    {
        return new FormTableFilter($this->criteriaManager, $this->getFiltersDeclarations());
    }

    /**
     * 
     * @return CriteriaManagerInterface
     */
    public function getCriteriaManager()
    {
        return $this->criteriaManager;
    }

    /**
     * 
     * @return FormTableFilter
     */
    public function getFilter()
    {
        return $this->filterManager;
    }

    /**
     * 
     * @return array
     */
    protected function getButtonsDeclarations()
    {
        return array();
    }

    /**
     * 
     * @return array
     */
    protected function getFiltersDeclarations()
    {
        return array();
    }

    /**
     * 
     * @return array
     */
    protected function constructButtons()
    {
        $buttons = array();
        $declarations = (array)$this->getButtonsDeclarations();
        foreach ($declarations as $id => $params) {
            $button = $this->createButton($id, $params);
            if ($button !== null) {
                $buttons[$button->getId()] = $button;
            }
        }
        return $buttons;
    }

    /**
     * 
     * @param string $id
     * @param array $params
     * @return FormTableButton
     */
    protected function createButton($id, $params)
    {
        if (isset($params['class'])) {
            $class = $params['class'];
            if (!class_exists($class, true)) {
                return null;
            }
        } else {
            $class = $this->getButtonClass();
        }
        return new $class($id, $params, $this);
    }

    /**
     * 
     * @return string
     */
    protected function getButtonClass()
    {
        return FormTableButton::class;
    }

    /**
     * 
     * @param string $id
     * @param array $params
     * @return FormFieldInterface
     */
    protected function createFilter($id, $params)
    {
        if ($params === true) {
            $field = $this->getField($id);
            if ($field) {
                $filter = clone $field;
            }
        } else {
            $filter = $this->createField($id, $params);
        }
        return $filter;
    }

    /**
     * 
     * @return array
     */
    public final function getButtons()
    {
        return $this->buttons;
    }

    /**
     * 
     * @param boolean $flag
     */
    public final function setIsControlEnabled($flag)
    {
        $this->isControlEnabled = (bool)$flag;
    }

    /**
     * 
     * @return boolean
     */
    public final function isControlEnabled()
    {
        return $this->isControlEnabled;
    }

    /**
     * 
     * @param boolean $flag
     */
    public final function setIsNavigationEnabled($flag)
    {
        return $this->criteriaManager->setIsNavigationEnabled($flag);
    }

    /**
     * 
     * @return boolean
     */
    public final function isNavigationEnabled()
    {
        return $this->criteriaManager->isNavigationEnabled();
    }

    /**
     * 
     * @param boolean $flag
     */
    public final function setIsFilterEnabled($flag)
    {
        return $this->criteriaManager->setIsFilterEnabled($flag);
    }

    /**
     * 
     * @return boolean
     */
    public final function isFilterEnabled()
    {
        return $this->criteriaManager->isFilterEnabled();
    }

    /**
     * 
     * @param boolean $flag
     */
    public final function setIsSortingEnabled($flag)
    {
        return $this->criteriaManager->setIsSortingEnabled($flag);
    }

    /**
     * 
     * @return boolean
     */
    public final function isSortingEnabled()
    {
        return $this->criteriaManager->isSortingEnabled();
    }

    /**
     * 
     * @param int $index
     */
    public final function setStartIndex($index)
    {
        $this->criteriaManager->setStartIndex($index);
    }

    /**
     * 
     * @return int
     */
    public final function getStartIndex()
    {
        return $this->criteriaManager->getStartIndex();
    }

    /**
     * 
     * @param int $onPage
     */
    public final function setOnPage($onPage)
    {
        $this->criteriaManager->setOnPage($onPage);
    }

    /**
     * 
     * @return int
     */
    public final function getOnPage()
    {
        return $this->criteriaManager->getOnPage();
    }

    /**
     * 
     * @param int $onPage
     */
    public function setCustomOnPage($onPage)
    {
        $this->criteriaManager->setCustomOnPage($onPage);
    }

    /**
     * 
     * @param int $page
     */
    public final function setPage($page)
    {
        $this->criteriaManager->setPage($page);
    }

    /**
     * 
     * @return int
     */
    public final function getPage()
    {
        return $this->criteriaManager->getPage();
    }

    /**
     * 
     * @param int $value
     */
    public final function setKeepOrderAmount($value)
    {
        $this->criteriaManager->setKeepOrderAmount($value);
    }

    /**
     * 
     * @return int
     */
    public final function getKeepOrderAmount()
    {
        return $this->criteriaManager->getKeepOrderAmount();
    }

    /**
     * 
     * @param int $value
     */
    public final function setNearbyPagesAmount($value)
    {
        $this->criteriaManager->setNearbyPagesAmount($value);
    }

    /**
     * 
     * @return int
     */
    public final function getNearbyPagesAmount()
    {
        return $this->criteriaManager->getNearbyPagesAmount();
    }

    /**
     * 
     * @param int $index
     * @return int
     */
    public final function getPageByIndex($index)
    {
        return $this->criteriaManager->getPageByIndex($index);
    }

    /**
     * 
     * @return int
     */
    public final function getTotalPages()
    {
        return $this->criteriaManager->getTotalPages();
    }

    /**
     * 
     * @return int
     */
    public final function getTotalRecords()
    {
        return $this->criteriaManager->getTotalRecords();
    }

    /**
     * 
     * @param boolean $short
     * @param boolean $nulls
     * @return array
     */
    public function getFilterParams($short = false, $nulls = false)
    {
        return $this->criteriaManager->getFilterParams($short, $nulls);
    }

    /**
     * 
     * @param array $input
     */
    public function setFilterInput($input)
    {
        $this->criteriaManager->setFilterInput($input);
    }

    /**
     * 
     * @param array $input
     * @return array
     */
    public function processOrderInput($input)
    {
        return $this->criteriaManager->processOrderInput($input);
    }

    /**
     * 
     * @param array $input
     * @return array
     */
    public function processWhereInput($input)
    {
        return $this->criteriaManager->processWhereInput($input);
    }

    /**
     * 
     * @param array $filter
     * @param boolean $replace
     */
    public function setOrderFilter($filter, $replace = true)
    {
        $this->criteriaManager->setOrderFilter($filter, $replace);
    }

    /**
     * 
     * @param array $filter
     * @param boolean $replace
     */
    public function setOrderFilterDefaults($filter, $replace = true)
    {
        $this->criteriaManager->setOrderFilterDefaults($filter, $replace);
    }

    /**
     * 
     * @param boolean $short
     * @return array
     */
    public function getOrderFilter($short = false)
    {
        return $this->criteriaManager->getOrderFilter($short);
    }

    /**
     * 
     * @param array $filter
     * @param boolean $replace
     */
    public function setWhereFilter($filter, $replace = true)
    {
        $this->criteriaManager->setWhereFilter($filter, $replace);
    }

    /**
     * 
     * @param array $filter
     * @param boolean $replace
     */
    public function setWhereFilterDefaults($filter, $replace = true)
    {
        $this->criteriaManager->setWhereFilterDefaults($filter, $replace);
    }

    /**
     * 
     * @param boolean $short
     * @return array
     */
    public function getWhereFilter($short = false)
    {
        return $this->criteriaManager->getWhereFilter($short);
    }

    /**
     * 
     * @param string $fieldId
     * @param boolean $onlyPrimary
     * @return mixed
     */
    public function getFieldOrderStatus($fieldId, $onlyPrimary = true)
    {
        return $this->criteriaManager->getFieldOrderStatus($fieldId, $onlyPrimary);
    }

    /**
     * 
     * @param string $baseUrl
     * @param boolean $keepQuery
     * @param array $params
     * @param boolean $makeShort
     * @return string
     */
    public function generateUrl($baseUrl, $keepQuery = true, $params = array(), $makeShort = true)
    {
        return $this->criteriaManager->generateUrl($baseUrl, $keepQuery, $params, $makeShort);
    }

    /**
     * 
     * @param string $baseUrl
     * @param boolean $keepQuery
     * @return string
     */
    public function generateCurrentUrl($baseUrl, $keepQuery = true)
    {
        return $this->criteriaManager->generateCurrentUrl($baseUrl, $keepQuery);
    }

    /**
     * 
     * @param string $baseUrl
     * @param string $fieldId
     * @return string
     */
    public function generateOrderSwitchUrl($baseUrl, $fieldId)
    {
        return $this->criteriaManager->generateOrderSwitchUrl($baseUrl, $fieldId);
    }

    /**
     * 
     * @param string $baseUrl
     * @param boolean $keepQuery
     * @return string
     */
    public function generateResetUrl($baseUrl, $keepQuery = true)
    {
        return $this->criteriaManager->generateResetUrl($baseUrl, $keepQuery);
    }

    /**
     * 
     * @param string $baseUrl
     * @param int $pagesToShow
     * @param string $pageKey
     * @return string
     */
    public function generateNavigation($baseUrl, $pagesToShow = null, $pageKey = null)
    {
        return $this->criteriaManager->generateNavigation($baseUrl, $pagesToShow, $pageKey);
    }

    /**
     * 
     * @param string $baseUrl
     * @param int $pagesToShow
     * @param string $pageKey
     * @return string
     */
    public function generateShortNavigation($baseUrl, $pagesToShow = null, $pageKey = null)
    {
        return $this->criteriaManager->generateShortNavigation($baseUrl, $pagesToShow, $pageKey);
    }

    /**
     * 
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->criteriaManager->setBaseUrl($baseUrl);
    }

    /**
     * 
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->criteriaManager->getBaseUrl();
    }

    /**
     * 
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->criteriaManager->getCurrentUrl();
    }

}
