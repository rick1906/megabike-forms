<?php

namespace megabike\forms;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

class FormData implements ArrayAccess, Countable, IteratorAggregate
{

    protected $data;
    protected $subset;

    /**
     * 
     * @var FormData
     */
    protected $root;

    /**
     * 
     * @var FormDataExtractor
     */
    protected $extractor;
    //
    protected $subsets = null;
    protected $model = false;
    protected $metadata = null;

    public final function __construct($data = [], $rootOrExtractor = null, $subset = '')
    {
        $this->data = (array)$data;
        $this->subset = (string)$subset;
        if ($rootOrExtractor instanceof FormDataExtractor) {
            $this->extractor = $rootOrExtractor;
            $this->root = null;
        } else {
            $this->extractor = null;
            $this->root = $rootOrExtractor;
        }
    }

    public function getName()
    {
        if ($this->extractor !== null) {
            return $this->extractor->extractName($this);
        }
        if ($this->root !== null && $this->root->extractor !== null) {
            return $this->root->extractor->extractName($this);
        }
        return null;
    }

    public function toArray()
    {
        return $this->data;
    }

    public function setMetadata($key, $value)
    {
        $this->metadata[$key] = $value;
    }

    public function getMetadata($key)
    {
        return isset($this->metadata[$key]) ? $this->metadata[$key] : null;
    }

    public function getModel()
    {
        if ($this->model === false) {
            $this->model = $this->createModel($this);
        }
        return $this->model;
    }

    public function setModel($model)
    {
        if ($model !== false) {
            $this->model = $model;
        } else {
            $this->model = null;
        }
    }

    public function getSubsetId()
    {
        return $this->subset;
    }

    public function getRootSubset()
    {
        return $this->root !== null ? $this->root : $this;
    }

    public function getSubsetOrCreate($key)
    {
        $subset = $this->getSubset($key);
        if ($subset === null) {
            $this->setSubset($key, []);
            return $this->getSubset($key);
        }
        return $subset;
    }

    public function getSubsetOrSelf($key)
    {
        $subset = $this->getSubset($key);
        return $subset !== null ? $subset : $this;
    }

    public function getSubset($key)
    {
        if ((string)$key === '') {
            return $this->getRootSubset();
        }
        if ($this->root !== null) {
            return $this->root->getSubset($key);
        }
        if ($this->subsets && array_key_exists($key, $this->subsets)) {
            return $this->subsets[$key];
        }
        if ($this->extractor !== null && $this->extractor->hasSubsetData($this, $key)) {
            $data = $this->extractor->getSubsetData($this, $key);
            $subset = $this->createSubset($data, $key);
            $this->subsets[$key] = $subset;
            return $subset;
        }
        return null;
    }

    public function hasSubset($key)
    {
        if ((string)$key === '') {
            return true;
        }
        if ($this->root !== null) {
            return $this->root->hasSubset($key);
        }
        if ($this->subsets && array_key_exists($key, $this->subsets)) {
            return true;
        }
        if ($this->extractor !== null && $this->extractor->hasSubsetData($this, $key)) {
            return true;
        }
        return false;
    }

    public function setSubset($key, $data)
    {
        if ($this->root !== null) {
            return $this->root->setSubset($key, $data);
        } else {
            $subset = $this->createSubset($data, $key);
            $this->subsets[$key] = $subset;
            return $subset;
        }
    }

    protected function createSubset($data, $key)
    {
        if ($data === null || $data === false) {
            return null;
        } elseif ($data instanceof $this) {
            return $data;
        } else {
            $class = get_class($this);
            return new $class($data, $this, $key);
        }
    }

    protected function createModel($subset)
    {
        if ($this->extractor !== null) {
            return $this->extractor->createModel($subset);
        }
        if ($this->root !== null) {
            return $this->root->createModel($subset);
        }
        return null;
    }

    public function isChanged($key, $oldData, $strict = false)
    {
        if ($this->keyExists($key)) {
            $value = $this[$key];
            if (isset($oldData[$key])) {
                return $strict ? $value !== $oldData[$key] : (string)$value !== (string)$oldData[$key];
            } else {
                return $value !== null;
            }
        }
        return false;
    }

    public function count()
    {
        return count($this->data);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    public function isEmpty()
    {
        return empty($this->data);
    }

    public function keyExists($offset)
    {
        return isset($this->data[$offset]) || array_key_exists($offset, $this->data);
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

}
