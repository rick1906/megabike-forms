<?php

namespace megabike\forms;

class BufferedCriteriaManager extends FieldsCriteriaManager
{

    protected $_currentOrderParams = null;
    protected $_staticData = null;
    protected $_rawData = null;
    protected $_rawDataCallback = null;
    protected $_transformCallback = null;

    public function setDataArray($dataArray)
    {
        $this->_rawData = (array)$dataArray;
    }

    public function setDataCallback($callback)
    {
        $this->_rawDataCallback = $callback;
    }

    public function setTransformCallback($callback)
    {
        $this->_transformCallback = $callback;
    }

    protected function loadRawData()
    {
        if ($this->_rawData !== null) {
            return $this->_rawData;
        }
        if ($this->_rawDataCallback !== null) {
            return (array)call_user_func($this->_rawDataCallback);
        }
        return [];
    }

    protected function transformRawItem($item)
    {
        if ($this->_transformCallback !== null) {
            return call_user_func($this->_transformCallback, $item);
        }
        return $item;
    }

    protected function loadStaticData()
    {
        $items = $this->loadRawData();
        $buffer = array();
        foreach ($items as $rawItem) {
            $item = $this->transformRawItem($rawItem);
            $buffer[] = $item;
        }
        return $buffer;
    }

    public function getStaticData()
    {
        if ($this->_staticData === null) {
            $this->_staticData = $this->loadStaticData();
        }
        return $this->_staticData;
    }

    public function getStaticItemByKey($key, $id)
    {
        foreach ($this->getStaticData() as $item) {
            if (isset($item[$key]) && $item[$key] === $id) {
                return $item;
            }
        }
        return null;
    }

    public function getBuffer()
    {
        $data1 = $this->applyFilterToBuffer($this->getStaticData());
        $data2 = $this->applyOrderToBuffer($data1);
        return $this->applyLimitToBuffer($data2);
    }

    protected function getSubsetItem($item, $key, $subset = null)
    {
        if (isset($this->fields[$key]) && $subset === null) {
            $subset = $this->fields[$key]->getSubset();
        }
        if ($item instanceof FormData && $subset !== null) {
            return $item->getSubsetOrSelf($subset);
        }
        if ($subset !== null && isset($item[$subset]) && is_array($item[$subset])) {
            return $item[$subset];
        }
        return $item;
    }

    protected function applyFilterToBuffer($data)
    {
        $where = $this->getWhereFilter();
        if ($where) {
            foreach ($where as $key => $opv) {
                if (is_array($opv)) {
                    list($op, $v) = $opv;
                    $source = $data;
                    $data = array();
                    foreach ($source as $originalItem) {
                        $item = $this->getSubsetItem($originalItem, $key);
                        if (!isset($item[$key])) {
                            continue;
                        }
                        if ($op === '=') {
                            $s1 = mb_strtolower((string)$v);
                            $s2 = mb_strtolower((string)$item[$key]);
                            if ($s1 !== $s2) {
                                continue;
                            }
                        } elseif ($op === 'LIKE%') {
                            $len = strlen((string)$v);
                            $s1 = mb_strtolower((string)$v);
                            $s2 = mb_strtolower(substr((string)$item[$key], 0, $len));
                            if ($s1 !== $s2) {
                                continue;
                            }
                        }
                        if ($op === '%LIKE%') {
                            $s1 = mb_strtolower((string)$v);
                            $s2 = mb_strtolower((string)$item[$key]);
                            if (strpos($s2, $s1) === false) {
                                continue;
                            }
                        }
                        $data[] = $item;
                    }
                }
            }
        }
        return $data;
    }

    protected function compareItemsOrder($item1, $item2)
    {
        $params = $this->_currentOrderParams;
        foreach ($params as $p) {
            list($key, $o, $t) = $p;
            $it1 = $this->getSubsetItem($item1, $key, $t);
            $it2 = $this->getSubsetItem($item2, $key, $t);
            $val1 = $it1[$key];
            $val2 = $it2[$key];
            if (is_numeric($val1) && is_numeric($val2)) {
                $cmp = strnatcmp($val1, $val2);
            } else {
                $val1 = mb_strtolower($val1);
                $val2 = mb_strtolower($val2);
                $cmp = strcmp($val1, $val2);
            }

            if ($o === 'desc') {
                $cmp = -$cmp;
            }
            if ($cmp !== 0) {
                return $cmp;
            }
        }
        return 0;
    }

    protected function applyOrderToBuffer($data)
    {
        $orderString = $this->getOrderString();
        if (is_array($orderString)) {
            $orderString = implode(', ', $orderString);
        }

        $m = null;
        $order = explode(',', $orderString);
        $params = array();
        foreach ($order as $os) {
            $o = trim($os);
            $to = explode('.', $o, 2);
            if (isset($to[1])) {
                $t = trim($to[0]);
                $o = trim($to[1]);
            } else {
                $t = null;
                $o = trim($to[0]);
            }
            if (preg_match('/^`([^`]+)`\s+(\w+)$/', $o, $m)) {
                $field = $m[1];
                $dir = strtolower($m[2]);
            } elseif (preg_match('/^(\w+)\s+(\w+)$/', $o, $m)) {
                $field = $m[1];
                $dir = strtolower($m[2]);
            } else {
                continue;
            }

            $params[] = array($field, $dir, $t);
        }

        if ($params) {
            $this->_currentOrderParams = $params;
            usort($data, array($this, 'compareItemsOrder'));
        }

        return $data;
    }

    protected function applyLimitToBuffer($data)
    {
        if ($this->isNavigationEnabled && $this->onPage > 0) {
            $start = $this->startIndex;
            $limit = $this->onPage;
            return array_slice($data, $start, $limit);
        } else {
            return $data;
        }
    }

    public function getTotalRecords()
    {
        if ($this->_totalRecords === null) {
            $items = $this->applyFilterToBuffer($this->getStaticData());
            $this->_totalRecords = count($items);
        }
        return $this->_totalRecords;
    }

}
