<?php

namespace megabike\forms;

use InvalidArgumentException;

abstract class AbstractCriteriaManager implements DbCriteriaManagerInterface
{

    protected $_totalRecords = null;
    protected $_status = null;
    //
    protected $isNavigationEnabled = true;
    protected $isFilterEnabled = true;
    protected $isSortingEnabled = true;
    protected $onPage = 20;
    protected $startIndex = 0;
    protected $keepOrderAmount = 1;
    protected $nearbyPagesAmount = 3;
    protected $baseUrl = null;
    //
    protected $queryParams = array();
    protected $customParams = array();
    protected $inputParams = array();

    public function __construct()
    {
        $this->queryParams = array(
            'select' => '*',
            'from' => '',
            'where' => null,
            'order' => null,
            'groupby' => null,
            'having' => null,
        );
        $this->inputParams = array(
            'order' => array('order', 'o'),
            'where' => array('where', 'w'),
            'page' => 'page',
            'start' => null,
            'onpage' => 'onpage',
        );
    }

    public function reset()
    {
        $this->_totalRecords = null;
        $this->_status = null;
    }

    public function setQuery($select, $from, $where = null, $order = null, $groupby = null, $having = null)
    {
        $this->queryParams = array(
            'select' => $select,
            'from' => $from,
            'where' => $where,
            'order' => $order,
            'groupby' => $groupby,
            'having' => $having,
        );
        $this->reset();
    }

    public function setCustomQuery($query)
    {
        $matches = null;
        $select = null;
        $from = null;
        $where = null;
        $order = null;
        $groupby = null;
        $having = null;
        if (preg_match('/^(.+)\s+LIMIT\s+(.+)$/isU', $query, $matches)) {
            $query = $matches[1];
        }
        if (preg_match('/^(.+)\s+ORDER BY\s+(.+)$/isU', $query, $matches)) {
            $query = $matches[1];
            $order = $matches[2];
        }
        if (preg_match('/^(.+)\s+HAVING\s+(.+)$/isU', $query, $matches)) {
            $query = $matches[1];
            $having = $matches[2];
        }
        if (preg_match('/^(.+)\s+GROUP BY\s+(.+)$/isU', $query, $matches)) {
            $query = $matches[1];
            $groupby = $matches[2];
        }
        if (preg_match('/^(.+)\s+WHERE\s+(.+)$/isU', $query, $matches)) {
            $query = $matches[1];
            $where = $matches[2];
        }
        if (preg_match('/^\s*SELECT\s+(.+)\s+FROM\s+(.+)$/isU', $query, $matches)) {
            $select = $matches[1];
            $from = $matches[2];
        } else {
            return false;
        }

        $this->setQuery($select, $from, $where, $order, $groupby, $having);
        return true;
    }

    public abstract function getFields();

    public abstract function getField($id);

    public function setDefaultOrder($order)
    {
        $this->queryParams['order'] = $order;
        $this->reset();
    }

    public function addDefaultOrder($order)
    {
        $this->queryParams['order'] = \megabike\db\DbManager::mergeOrder($this->queryParams['order'], $order);
        $this->reset();
    }

    public function setDefaultWhere($where)
    {
        $this->queryParams['where'] = $where;
        $this->reset();
    }

    public function addDefaultWhere($where)
    {
        $this->queryParams['where'] = \megabike\db\DbManager::mergeWhere($this->queryParams['where'], $where);
        $this->reset();
    }

    protected function getFixedOrder()
    {
        return null;
    }

    protected function getFixedWhere()
    {
        return null;
    }

    protected function getOrderString()
    {
        $order = $this->getFixedOrder();
        $order1 = $this->queryParams['order'];
        $order2 = $this->getCustomOrder();
        if (is_array($order1) && !empty($order1) || !is_array($order1) && (string)$order1 !== '') {
            $order = \megabike\db\DbManager::mergeOrder($order, $order1);
        }
        if (is_array($order2) && !empty($order2) || !is_array($order2) && (string)$order2 !== '') {
            $order = \megabike\db\DbManager::mergeOrder($order, $order2);
        }
        return \megabike\db\DbManager::db()->buildOrderString($order);
    }

    protected function getWhereString()
    {
        $where = $this->getFixedWhere();
        $where1 = $this->queryParams['where'];
        $where2 = $this->getCustomWhere();
        if (is_array($where1) && !empty($where1) || !is_array($where1) && (string)$where1 !== '') {
            $where = \megabike\db\DbManager::mergeWhere($where, $where1);
        }
        if (is_array($where2) && !empty($where2) || !is_array($where2) && (string)$where2 !== '') {
            $where = \megabike\db\DbManager::mergeWhere($where, $where2);
        }
        return \megabike\db\DbManager::db()->buildWhereString($where);
    }

    protected function getLimitString()
    {
        return $this->isNavigationEnabled && $this->onPage > 0 ? ($this->startIndex.','.$this->onPage) : null;
    }

    public function getCountQuery()
    {
        $select = 'count(*) as count';
        $from = $this->queryParams['from'];
        $where = $this->getWhereString();
        return \megabike\db\DbManager::createSelect($select, $from, $where);
    }

    public function getSingleRecordQuery($where)
    {
        $select = $this->queryParams['select'];
        $from = $this->queryParams['from'];
        $where = \megabike\db\DbManager::mergeWhere($this->getWhereString(), $where);
        return \megabike\db\DbManager::createSelect($select, $from, $where, null, '0,1');
    }

    public function getQuery($withLimit = true)
    {
        $select = $this->queryParams['select'];
        $from = $this->queryParams['from'];
        $where = $this->getWhereString();
        $order = $this->getOrderString();
        $groupby = $this->queryParams['groupby'];
        $having = $this->queryParams['having'];
        if ($withLimit) {
            $limit = $this->getLimitString();
            return \megabike\db\DbManager::createSelect($select, $from, $where, $order, $limit, $groupby, $having);
        } else {
            return \megabike\db\DbManager::createSelect($select, $from, $where, $order, null, $groupby, $having);
        }
    }

    public final function setIsNavigationEnabled($flag)
    {
        $this->isNavigationEnabled = (bool)$flag;
    }

    public final function isNavigationEnabled()
    {
        return $this->isNavigationEnabled;
    }

    public final function setIsFilterEnabled($flag)
    {
        $this->isFilterEnabled = (bool)$flag;
    }

    public final function isFilterEnabled()
    {
        return $this->isFilterEnabled;
    }

    public final function setIsSortingEnabled($flag)
    {
        $this->isSortingEnabled = (bool)$flag;
    }

    public final function isSortingEnabled()
    {
        return $this->isSortingEnabled;
    }

    public final function setStartIndex($index, $throw = true)
    {
        if (is_numeric($index) && $index >= 0) {
            $this->startIndex = (int)$index;
        } elseif (!$throw) {
            $this->startIndex = 0;
        } else {
            throw new InvalidArgumentException("Parameter 'startIndex' should be a non-negative number");
        }
        $this->reset();
    }

    public final function getStartIndex()
    {
        return $this->startIndex;
    }

    public final function setOnPage($onPage, $throw = true)
    {
        if (is_numeric($onPage) && $onPage >= 1) {
            $this->onPage = (int)$onPage;
        } elseif (empty($onPage)) {
            $this->onPage = 0;
        } elseif (!$throw) {
            $this->onPage = 0;
        } else {
            throw new InvalidArgumentException("Parameter 'onPage' should be a non-negative number");
        }
        $this->reset();
    }

    public function setCustomOnPage($onPage)
    {
        if ($this->isNavigationEnabled) {
            $oldValue = $this->onPage;
            $this->setOnPage($onPage, false);
            if ($this->onPage !== $oldValue) {
                $this->customParams['onPage'] = $this->onPage;
            }
        }
    }

    public final function getOnPage()
    {
        return $this->onPage;
    }

    public final function setPage($page, $throw = true)
    {
        if ($this->onPage > 0) {
            if (is_numeric($page) && $page >= 1) {
                $this->startIndex = $this->onPage * ($page - 1);
            } elseif (!$throw) {
                $this->startIndex = 0;
            } else {
                throw new InvalidArgumentException("Parameter 'page' should be a positive number");
            }
            $this->reset();
        }
    }

    public final function getPage()
    {
        return $this->getPageByIndex($this->startIndex);
    }

    public final function setKeepOrderAmount($value, $throw = true)
    {
        if (is_numeric($value) && $value >= 0) {
            $this->keepOrderAmount = (int)$value;
        } elseif (!$throw) {
            $this->keepOrderAmount = 1;
        } else {
            throw new InvalidArgumentException("Parameter 'keepOrderAmount' should be a non-negative number");
        }
    }

    public final function getKeepOrderAmount()
    {
        return $this->keepOrderAmount;
    }

    public final function setNearbyPagesAmount($value, $throw = true)
    {
        if (is_numeric($value) && $value >= 1) {
            $this->nearbyPagesAmount = (int)$value;
        } elseif (!$throw) {
            $this->nearbyPagesAmount = 3;
        } else {
            throw new InvalidArgumentException("Parameter 'nearbyPagesAmount' should be a positive number");
        }
    }

    public final function getNearbyPagesAmount()
    {
        return $this->nearbyPagesAmount;
    }

    public final function getPageByIndex($index)
    {
        return $this->onPage > 0 ? (1 + (int)floor($index / $this->onPage)) : 1;
    }

    public function getTotalRecords()
    {
        if ($this->_totalRecords === null) {
            $row = \megabike\db\DbManager::queryRow($this->getCountQuery());
            $this->_totalRecords = $row && isset($row['count']) ? $row['count'] : 0;
        }
        return $this->_totalRecords;
    }

    public function getTotalPages()
    {
        $total = $this->getTotalRecords();
        if ($total > 0) {
            return $this->onPage > 0 ? (1 + (int)floor(($total - 1) / $this->onPage)) : 1;
        }
        return 0;
    }

    protected abstract function getFieldOperators($field);

    protected abstract function generateWhereCondition($value, $operator, $field);

    protected function buildCustomWhere($whereParams, $fields, &$whereStatus = null)
    {
        $whereConditions = array();

        foreach ($whereParams as $key => $value) {
            if (is_int($key) || !isset($fields[$key]) || isset($whereStatus[$key])) {
                continue;
            }

            $field = $fields[$key];
            $operators = $this->getFieldOperators($field);
            if (!$operators) {
                continue;
            }

            if (is_array($value) && isset($value[0]) && isset($value[1]) && count($value) == 2) {
                $operator = strtoupper($value[0]);
                if ($operator === '=' || in_array($operator, $operators)) { // TODO: better op recognition
                    $val = $value[1];
                } else {
                    $operator = '=';
                    $val = $value;
                }
            } else {
                $operator = '=';
                $val = $value;
            }

            if (in_array($operator, $operators)) {
                $realOperator = $operator;
            } elseif ($operator === '=' && count($operators) <= 1) {
                $realOperator = $operators[0];
            } else {
                continue;
            }

            $whereStatus[$key] = array($operator, $val);
            $condition = $this->generateWhereCondition($val, $realOperator, $fields[$key]);
            if ((string)$condition !== '') {
                $whereConditions[] = $condition;
            }
        }

        return $whereConditions;
    }

    protected final function getCustomWhere()
    {
        if (!isset($this->_status['where'])) {
            $this->_status['whereFilter'] = array();
            $where = array();
            if (!empty($this->customParams['where'])) {
                $where = $this->buildCustomWhere($this->customParams['where'], $this->getFields(), $this->_status['whereFilter']);
            }
            if (!empty($this->customParams['defaultWhere'])) {
                $whereStatus = $this->_status['whereFilter'];
                $defaultWhere = $this->buildCustomWhere($this->customParams['defaultWhere'], $this->getFields(), $whereStatus);
                $where = array_merge($where, $defaultWhere);
            }
            $this->_status['where'] = $where;
        }
        return $this->_status['where'];
    }

    public function processWhereInput($input)
    {
        $filter = array();
        $this->buildCustomWhere($input, $filter);
        return $filter;
    }

    public function getWhereFilter($short = false)
    {
        $this->getCustomWhere();
        if (!$short) {
            return $this->_status['whereFilter'];
        } else {
            return $this->shortenInput($this->_status['whereFilter'], 'where');
        }
    }

    public function getWhereFilterValues()
    {
        $this->getCustomWhere();
        $values = array();
        foreach ($this->_status['whereFilter'] as $key => $val) {
            $values[$key] = $val[1];
        }
        return $values;
    }

    public function setWhereFilter($filter, $replace = true)
    {
        if (isset($this->customParams['where']) && !$replace) {
            $this->customParams['where'] = array_merge($this->customParams['where'], $filter);
        } else {
            $this->customParams['where'] = $filter;
        }
        $this->reset();
    }

    public function setWhereFilterDefaults($filter, $replace = true)
    {
        if (isset($this->customParams['defaultWhere']) && !$replace) {
            $this->customParams['defaultWhere'] = array_merge($this->customParams['defaultWhere'], $filter);
        } else {
            $this->customParams['defaultWhere'] = $filter;
        }
        $this->reset();
    }

    protected abstract function getFieldDefaultOrder($field);

    protected abstract function generateOrderExpression($order, $field);

    protected function buildCustomOrder($orderParams, $fields, &$orderStatus = null)
    {
        $orderExpressions = array();

        foreach ($orderParams as $key => $value) {
            if (is_int($key) || !isset($fields[$key]) || isset($orderStatus[$key])) {
                continue;
            }

            $field = $fields[$key];
            $value = strtolower($value);
            if ($value === 'a' || $value === 'asc') {
                $o = 'asc';
            } elseif ($value === 'd' || $value === 'desc') {
                $o = 'desc';
            } else {
                $fo = $this->getFieldDefaultOrder($field);
                if ($fo) {
                    $o = substr($fo, 0, 1) === 'd' ? 'desc' : 'asc';
                } else {
                    continue;
                }
            }

            $orderStatus[$key] = $o;
            $expression = $this->generateOrderExpression($o, $fields[$key]);
            if ((string)$expression !== '') {
                $orderExpressions[] = $expression;
            }
        }

        return $orderExpressions;
    }

    protected final function getCustomOrder()
    {
        if (!isset($this->_status['order'])) {
            $this->_status['orderFilter'] = array();
            $order = array();
            if (!empty($this->customParams['order'])) {
                $order = $this->buildCustomOrder($this->customParams['order'], $this->getFields(), $this->_status['orderFilter']);
            }
            if (!empty($this->customParams['defaultOrder'])) {
                $orderStatus = $this->_status['orderFilter'];
                $defaultOrder = $this->buildCustomOrder($this->customParams['defaultOrder'], $this->getFields(), $orderStatus);
                $order = array_merge($order, $defaultOrder);
            }
            $this->_status['order'] = $order;
        }
        return $this->_status['order'];
    }

    protected final function mergeCustomOrder($order, $newOrder)
    {
        $result = $newOrder;
        foreach ($order as $k => $v) {
            if (!isset($result[$k])) {
                $result[$k] = $v;
            }
        }
        return $result;
    }

    public function processOrderInput($input)
    {
        $filter = array();
        $this->buildCustomOrder($input, $filter);
        return $filter;
    }

    public function getOrderFilter($short = false)
    {
        $this->getCustomOrder();
        if (!$short) {
            return $this->_status['orderFilter'];
        } else {
            return $this->shortenInput($this->_status['orderFilter'], 'order');
        }
    }

    public function setOrderFilter($filter, $replace = true)
    {
        if (isset($this->customParams['order']) && !$replace) {
            $this->customParams['order'] = $this->mergeCustomOrder($this->customParams['order'], $filter);
        } else {
            $this->customParams['order'] = $filter;
        }
        $this->reset();
    }

    public function setOrderFilterDefaults($filter, $replace = true)
    {
        if (isset($this->customParams['defaultOrder']) && !$replace) {
            $this->customParams['defaultOrder'] = $this->mergeCustomOrder($this->customParams['defaultOrder'], $filter);
        } else {
            $this->customParams['defaultOrder'] = $filter;
        }
        $this->reset();
    }

    protected function getInputKey($type, $inputKeys, $input = false, $short = false)
    {
        if ($inputKeys !== null && array_key_exists($type, $inputKeys)) {
            $keys = $inputKeys[$type];
        } else {
            $keys = $this->inputParams[$type];
        }
        if ($input !== false) {
            if (is_array($keys)) {
                foreach ($keys as $key) {
                    if (isset($input[$key])) {
                        return $key;
                    }
                }
                return null;
            } else {
                return $keys !== null && isset($input[$keys]) ? $keys : null;
            }
        } elseif ($short && is_array($keys)) {
            $shortKey = null;
            foreach ($keys as $key) {
                if ($shortKey === null || strlen($key) < strlen($shortKey)) {
                    $shortKey = $key;
                }
            }
            return $shortKey;
        } else {
            if (is_array($keys)) {
                return !empty($keys) ? $keys[0] : null;
            } else {
                return $keys;
            }
        }
    }

    public function setInputKeys($type, $keys)
    {
        $this->inputParams[$type] = $keys;
    }

    public function shortenInput($input, $inputKeys = null)
    {
        if (!is_array($inputKeys)) {
            if ($inputKeys === 'where') {
                $filter = array();
                foreach ($input as $key => $val) {
                    if (is_array($val) && $val[0] === '=') {
                        $filter[$key] = $val[1];
                    } else {
                        $filter[$key] = $val;
                    }
                }
                return $filter;
            } elseif ($inputKeys === 'order') {
                $filter = array();
                foreach ($input as $key => $val) {
                    if (is_string($val)) {
                        $filter[$key] = $val[0];
                    } else {
                        $filter[$key] = $val;
                    }
                }
                return $filter;
            } else {
                return $input;
            }
        } else {
            $startKey = $this->getInputKey('start', $inputKeys, $input);
            $startNewKey = $this->getInputKey('start', $inputKeys, false, true);
            if ($startKey !== null && $startNewKey !== $startKey) {
                $input[$startNewKey] = $input[$startKey];
                unset($input[$startKey]);
            }
            $pageKey = $this->getInputKey('page', $inputKeys, $input);
            $pageNewKey = $this->getInputKey('page', $inputKeys, false, true);
            if ($pageKey !== null && $pageKey !== $pageNewKey) {
                $input[$pageNewKey] = $input[$pageKey];
                unset($input[$pageKey]);
            }
            $onpageKey = $this->getInputKey('onpage', $inputKeys, $input);
            $onpageNewKey = $this->getInputKey('onpage', $inputKeys, false, true);
            if ($onpageKey !== null && $onpageKey !== $onpageNewKey) {
                $input[$onpageNewKey] = $input[$onpageKey];
                unset($input[$onpageKey]);
            }
            $whereKey = $this->getInputKey('where', $inputKeys, $input);
            $whereNewKey = $this->getInputKey('where', $inputKeys, false, true);
            if ($whereKey !== null && $whereKey !== $whereNewKey) {
                $input[$whereNewKey] = $input[$whereKey];
                unset($input[$whereKey]);
            }
            if ($whereKey !== null && $whereNewKey !== null) {
                $input[$whereNewKey] = $this->shortenInput($input[$whereNewKey], 'where');
            }
            $orderKey = $this->getInputKey('order', $inputKeys, $input);
            $orderNewKey = $this->getInputKey('order', $inputKeys, false, true);
            if ($orderKey !== null && $orderKey !== $orderNewKey) {
                $input[$orderNewKey] = $input[$orderKey];
                unset($input[$orderKey]);
            }
            if ($orderKey !== null && $orderNewKey !== null) {
                $input[$orderNewKey] = $this->shortenInput($input[$orderNewKey], 'order');
            }
            return $input;
        }
    }

    public function setFilterInput($input, $inputKeys = null)
    {
        if ($this->isNavigationEnabled) {
            $onpageKey = $this->getInputKey('onpage', $inputKeys, $input);
            if ($onpageKey !== null) {
                $this->setCustomOnPage($input[$onpageKey]);
            }
            $startKey = $this->getInputKey('start', $inputKeys, $input);
            if ($startKey !== null) {
                $this->setStartIndex($input[$startKey], false);
            } else {
                $pageKey = $this->getInputKey('page', $inputKeys, $input);
                if ($pageKey !== null) {
                    $this->setPage($input[$pageKey], false);
                }
            }
        }
        if ($this->isFilterEnabled) {
            $whereKey = $this->getInputKey('where', $inputKeys, $input);
            if ($whereKey !== null) {
                $this->setWhereFilter($input[$whereKey]);
            }
        }
        if ($this->isSortingEnabled) {
            $orderKey = $this->getInputKey('order', $inputKeys, $input);
            if ($orderKey !== null) {
                $this->setOrderFilter($input[$orderKey]);
            }
        }
    }

    public function getFilterParams($short = false, $nulls = false, $inputKeys = null)
    {
        $filter = array();
        $startKey = $this->getInputKey('start', $inputKeys, false, $short);
        if ($startKey !== null) {
            if ($this->getStartIndex() > 0) {
                $filter[$startKey] = $this->getStartIndex();
            } elseif ($nulls) {
                $filter[$startKey] = null;
            }
        } else {
            $pageKey = $this->getInputKey('page', $inputKeys, false, $short);
            if ($pageKey !== null) {
                if ($this->getPage() > 1) {
                    $filter[$pageKey] = $this->getPage();
                } elseif ($nulls) {
                    $filter[$pageKey] = null;
                }
            }
        }
        $onpageKey = $this->getInputKey('onpage', $inputKeys, false, $short);
        if ($onpageKey !== null && isset($this->customParams['onPage'])) {
            $filter[$onpageKey] = $this->customParams['onPage'];
        }
        $whereKey = $this->getInputKey('where', $inputKeys, false, $short);
        if ($whereKey !== null) {
            $where = $this->getWhereFilter($short);
            $filter[$whereKey] = $where;
        }
        $orderKey = $this->getInputKey('order', $inputKeys, false, $short);
        if ($orderKey !== null) {
            $order = $this->getOrderFilter($short);
            $filter[$orderKey] = $order;
        }
        return $filter;
    }

    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    public function getBaseUrl()
    {
        if ($this->baseUrl === null) {
            $url = $this->getCurrentUrl();
            $parsed = parse_url($url);
            return isset($parsed['path']) ? $parsed['path'] : '/';
        } else {
            return $this->baseUrl;
        }
    }

    public function getCurrentUrl()
    {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    }

    public function generateUrl($baseUrl, $keepQuery = true, $params = array(), $makeShort = true, $inputKeys = null)
    {
        if ((string)$baseUrl === '') {
            $baseUrl = $this->getCurrentUrl();
        }

        $linkData = parse_url($baseUrl);
        $linkParams = array();
        if (isset($linkData['query'])) {
            if (!empty($keepQuery)) {
                parse_str($linkData['query'], $linkParams);
                if (is_array($keepQuery)) {
                    $newParams = array();
                    foreach ($keepQuery as $k => $p) {
                        if (isset($linkParams[$p])) {
                            $newParams[$p] = $linkParams[$p];
                        }
                    }
                    $linkParams = $newParams;
                }
            }
        }

        if ($makeShort) {
            $params = $this->shortenInput($params, $inputKeys);
        }

        $url = '';
        if (isset($linkData['scheme'])) {
            $url .= $linkData['scheme'].'://';
        } elseif (isset($linkData['host'])) {
            $url .= 'http://';
        }
        if (isset($linkData['host'])) {
            $url .= $linkData['host'];
            if (isset($linkData['port'])) {
                $url .= ':'.$linkData['port'];
            }
        }
        if (isset($linkData['path'])) {
            $url .= $linkData['path'];
        } else {
            $url .= '/';
        }

        foreach ($params as $k => $v) {
            if ($v === null) {
                unset($linkParams[$k]);
            } else {
                $linkParams[$k] = $v;
            }
        }
        if ($linkParams) {
            $q = http_build_query($linkParams);
            if ((string)$q !== '') {
                $url .= '?'.$q;
            }
        }

        return $url;
    }

    public function generateResetUrl($baseUrl, $keepQuery = true)
    {
        $onpageKey = $this->getInputKey('onpage', null, false, true);
        $whereKey = $this->getInputKey('where', null, false, true);
        $orderKey = $this->getInputKey('order', null, false, true);
        $params = array();
        $params[$onpageKey] = null;
        $params[$whereKey] = null;
        $params[$orderKey] = null;
        return $this->generateUrl($baseUrl, $keepQuery, $params, true);
    }

    public function generateCurrentUrl($baseUrl, $keepQuery = true)
    {
        $params = $this->getFilterParams(true, true);
        return $this->generateUrl($baseUrl, $keepQuery, $params, true);
    }

    public function getFieldOrderStatus($fieldId, $onlyPrimary = true)
    {
        $filter = $this->getOrderFilter(false);
        if (isset($filter[$fieldId])) {
            if (key($filter) === $fieldId || !$onlyPrimary) {
                $o = $filter[$fieldId];
            } else {
                $o = '';
            }
        } elseif (isset($this->customParams['defaultOrder'][$fieldId])) {
            if (empty($filter) && key($this->customParams['defaultOrder']) === $fieldId || !$onlyPrimary) {
                $o = $this->customParams['defaultOrder'][$fieldId];
            } else {
                $o = '';
            }
        } else {
            $o = '';
        }
        return $o;
    }

    public function generateOrderSwitchUrl($baseUrl, $fieldId)
    {
        $def = $this->getFieldOrderStatus($fieldId, false);
        if ($def === '') {
            $def = 'asc';
            $field = $this->getField($fieldId);
            if ($field) {
                $fo = $this->getFieldDefaultOrder($field);
                $def = $fo && substr($fo, 0, 1) === 'd' ? 'desc' : 'asc';
            }
        }

        $o = $this->getFieldOrderStatus($fieldId);
        $x = substr($o, 0, 1);
        if ($x === 'a') {
            $by = 'desc';
        } elseif ($x === 'd') {
            $by = 'asc';
        } else {
            $by = $def;
        }

        $filter = $this->getOrderFilter(false);
        unset($filter[$fieldId]);
        $orderFields = array_keys($filter);
        $lastIds = array_slice($orderFields, 0, $this->keepOrderAmount);

        $newFilter = array();
        $newFilter[$fieldId] = $by;
        if ($lastIds) {
            foreach ($lastIds as $lastId) {
                $newFilter[$lastId] = $filter[$lastId];
            }
        }

        $key = $this->getInputKey('order', null, false, true);
        return $this->generateUrl($baseUrl, true, array($key => $newFilter), true, array('order' => $key));
    }

    protected function generateNavigationSwitch($baseUrl, $type, $page, $current, $pageKey)
    {
        if ($page > 0) {
            if ($page == 1) {
                $params = array($pageKey => null);
            } else {
                $params = array($pageKey => $page);
            }
            $url = $this->generateUrl($baseUrl, true, $params, false, array('page' => $pageKey));
        } else {
            $url = '';
        }
        if ($type === 'switch') {
            $content = $page < $current ? '&#9194;' : '&#9193;';
        } elseif ($type === 'space') {
            $content = '...';
        } else {
            $content = $page;
        }
        return array(
            'type' => $type,
            'page' => $page,
            'url' => $url,
            'active' => ($page == $current) ? 1 : 0,
            'content' => $content,
        );
    }

    public function generateShortNavigation($baseUrl, $pagesToShow = null, $pageKey = null)
    {
        if ($this->onPage <= 0) {
            return array();
        }
        if ($pagesToShow === null) {
            $pagesToShow = $this->nearbyPagesAmount;
        }
        if ($pageKey === null) {
            $pageKey = $this->getInputKey('page', null, false, false);
        }

        $page = $this->getPage();
        $totalPages = $this->getTotalPages();

        if ($totalPages == 1) {
            return array();
        }

        $x = ($page - 1) % $pagesToShow;
        $sp = $page - $x;
        $ep = $sp + $pagesToShow - 1;
        $nav = array();
        if ($sp > 1) {
            $nav[] = $this->generateNavigationSwitch($baseUrl, 'switch', $sp - 1, $page, $pageKey);
        }
        for ($i = $sp; $i <= $ep && $i <= $totalPages; ++$i) {
            $nav[] = $this->generateNavigationSwitch($baseUrl, 'page', $i, $page, $pageKey);
        }
        if ($ep < $totalPages) {
            $nav[] = $this->generateNavigationSwitch($baseUrl, 'switch', $ep + 1, $page, $pageKey);
        }
        return $nav;
    }

    public function generateNavigation($baseUrl, $pagesToShow = null, $pageKey = null)
    {
        if ($this->onPage <= 0) {
            return array();
        }
        if ($pagesToShow === null) {
            $pagesToShow = $this->nearbyPagesAmount;
        }
        if ($pageKey === null) {
            $pageKey = $this->getInputKey('page', null, false, false);
        }

        $page = $this->getPage();
        $totalPages = $this->getTotalPages();

        if ($totalPages == 1) {
            return array();
        }

        $nav = array();
        $i = 1;
        for (; $i <= $pagesToShow && $i <= $totalPages; ++$i) {
            $nav[] = $this->generateNavigationSwitch($baseUrl, 'page', $i, $page, $pageKey);
        }
        if ($i < $page - $pagesToShow) {
            $i = $page - $pagesToShow + 1;
            $nav[] = $this->generateNavigationSwitch($baseUrl, 'space', 0, $page, $pageKey);
        }
        for (; $i <= $page + $pagesToShow - 1 && $i <= $totalPages - $pagesToShow; ++$i) {
            $nav[] = $this->generateNavigationSwitch($baseUrl, 'page', $i, $page, $pageKey);
        }
        if ($i < $totalPages - $pagesToShow) {
            $i = $totalPages - $pagesToShow + 1;
            $nav[] = $this->generateNavigationSwitch($baseUrl, 'space', 0, $page, $pageKey);
        }
        for (; $i <= $totalPages; ++$i) {
            $nav[] = $this->generateNavigationSwitch($baseUrl, 'page', $i, $page, $pageKey);
        }
        return $nav;
    }

}
