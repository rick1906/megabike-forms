<?php

namespace megabike\forms\fields;

use BadMethodCallException;
use megabike\forms\FieldInterface;

abstract class AbstractField implements FieldInterface
{

    protected $id;
    protected $subset = null;
    protected $name = null;
    protected $orderParams = null;
    protected $_attributes = array();

    public final function __construct($id, $attributes = array(), $parent = null)
    {
        $this->id = $id;
        foreach ($attributes as $key => $value) {
            if ($key === 'id') {
                throw new BadMethodCallException("Attributes must not contain 'id' key");
            } else {
                $this->setAttribute($key, $value);
            }
        }
        if ($parent !== null) {
            $this->initializeMetadata($parent);
        }
        $this->initialize();
    }

    protected function initialize()
    {
        
    }

    protected function initializeMetadata($object)
    {
        
    }

    public final function hasAttribute($name)
    {
        if (!is_int($name) && isset($name[0]) && $name[0] !== '_' && property_exists($this, $name)) {
            return $this->{$name} !== null;
        } else {
            return isset($this->_attributes[$name]);
        }
    }

    public final function getAttribute($name, $defaultValue = null)
    {
        if (!is_int($name) && isset($name[0]) && $name[0] !== '_' && property_exists($this, $name)) {
            return isset($this->{$name}) ? $this->{$name} : $defaultValue;
        } else {
            return isset($this->_attributes[$name]) ? $this->_attributes[$name] : $defaultValue;
        }
    }

    public final function setAttribute($name, $value)
    {
        if (!is_int($name) && isset($name[0]) && $name[0] !== '_' && property_exists($this, $name)) {
            $this->{$name} = $value;
        } else {
            $this->_attributes[$name] = $value;
        }
    }

    public final function removeAttribute($name)
    {
        if (!is_int($name) && isset($name[0]) && $name[0] !== '_' && property_exists($this, $name)) {
            $this->{$name} = null;
        } else {
            unset($this->_attributes[$name]);
        }
    }

    public final function offsetExists($offset)
    {
        return $this->hasAttribute($offset);
    }

    public final function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    public final function offsetSet($offset, $value)
    {
        return $this->setAttribute($offset, $value);
    }

    public final function offsetUnset($offset)
    {
        return $this->removeAttribute($offset);
    }

    public final function getId()
    {
        return $this->id;
    }

    public final function getSubset()
    {
        return $this->subset;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getShortName()
    {
        return $this->getName();
    }

    public function isRealField()
    {
        return false;
    }

    public function isOrderField()
    {
        return $this->orderParams !== null;
    }

    public function isWhereField()
    {
        return false;
    }

    public function isShow()
    {
        return $this->getAttribute('isShow', true);
    }

    public function isShowFull()
    {
        return $this->getAttribute('isShowFull', $this->isShow() || $this->isRealField());
    }

    public function isShowInForm()
    {
        return $this->getAttribute('isShowInForm', $this->isShow() || $this->isRealField());
    }

    public function getNote()
    {
        return $this->getAttribute('note', $this->getAttribute('help'));
    }

    public function getExtendedComment()
    {
        return '';
    }

    public abstract function getTextValue($object);

    public abstract function getNormalValue($object);

    public function getHtmlValue($object)
    {
        return html_encode($this->getTextValue($object));
    }

    public function getFullHtmlValue($object)
    {
        return $this->getHtmlValue($object);
    }

    public function getWhereOperators()
    {
        return array('=');
    }

    public function getOrderDefaultDirection()
    {
        return $this->getAttribute('orderDirection');
    }

    public function getFieldExpression($tableAlias = '')
    {
        return null;
    }

    public function getWhereCondition($value, $operator, $tableAlias = '')
    {
        return null;
    }

    public function getOrderExpression($order, $tableAlias = '')
    {
        if ($this->orderParams !== null) {
            $reverse = $order && !strncasecmp($order, "d", 1) ? 'asc' : 'desc';
            $orderArray = array();
            foreach ((array)$this->orderParams as $k => $v) {
                if (is_int($k)) {
                    $orderArray[$v] = $order;
                } elseif (is_bool($v)) {
                    $orderArray[$k] = $v ? $order : $reverse;
                } elseif ($v && !strncasecmp($v, "d", 1)) {
                    $orderArray[$k] = $reverse;
                } else {
                    $orderArray[$k] = $order;
                }
            }
            return \megabike\db\DbManager::db()->buildOrderString($orderArray);
        }
        return null;
    }

}
