<?php

namespace megabike\forms\fields;

use megabike\forms\FormSettings;

class DynamicValueField extends AbstractFormField
{
    protected $typeField = 'type';
    protected $subFields = array();
    protected $lastUsedType = null;

    protected function initialize()
    {
        $class = get_class($this);
        if (isset($_SESSION[$class]['lastUsedType'])) {
            $this->lastUsedType = $_SESSION[$class]['lastUsedType'];
        }
    }
    
    protected function getCharset()
    {
        return FormSettings::getInstance()->getCharset();
    }

    /**
     * @param string $type
     * @return AbstractFormField
     */
    protected function getSubField($type)
    {
        $type = (string)$type;
        $this->lastUsedType = $type;
        $_SESSION[get_class($this)]['lastUsedType'] = $type;

        if (isset($this->subFields[$type])) {
            return $this->subFields[$type];
        }

        if (strpos($type, '[') === false && strpos($type, '{') === false) {
            $params = array('type' => $type);
        } else {
            $p = iconv($this->getCharset(), 'utf-8', trim($type));
            $params = json_decode($p, true);
            $this->decodeCharset($params, 'utf-8');
        }

        if (!$params) {
            $params = array('type' => 'string');
        }

        if (!isset($params['required'])) {
            $params['required'] = $this->isRequired();
        }
        if (!isset($params['name'])) {
            $params['name'] = $this->getName();
        }
        if (!isset($params['field']) && $this->hasAttribute('field')) {
            $params['field'] = $this->getAttribute('field');
        }
        if (!isset($params['type'])) {
            $params['type'] = 'string';
        }
        if (!isset($params['class'])) {
            $params['class'] = ucfirst($params['type']).'Field';
        }

        $class = $params['class'];
        $field = new $class($this->id, $params);
        if (!$field && $type !== 'string') {
            return $this->getSubField('string');
        }

        $this->subFields[$type] = $field;
        return $field;
    }

    protected function decodeCharset(&$data, $in_charset)
    {
        if (!is_array($data)) {
            $data = iconv($in_charset, $this->getCharset(), $data);
        } else {
            foreach ($data as $k => &$v) {
                if (!is_array($v)) {
                    $v = iconv($in_charset, $this->getCharset(), $v);
                } else {
                    $this->decodeCharset($v, $in_charset);
                }
            }
        }
    }

    /**
     * @param array $object
     * @return AbstractFormField
     */
    protected function getSubFieldByData($object, $input = null)
    {
        if (isset($object[$this->typeField])) {
            return $this->getSubField($object[$this->typeField]);
        } elseif (isset($this->tableData['key']) && isset($this->tableData['table']) && isset($object[$this->tableData['key']])) {
            return $this->getSubFieldByPk($object[$this->tableData['key']]);
        } elseif (isset($input[$this->typeField])) {
            return $this->getSubField($input[$this->typeField]);
        } elseif (isset($this->tableData['key']) && isset($this->tableData['table']) && isset($input[$this->tableData['key']])) {
            return $this->getSubFieldByPk($input[$this->tableData['key']]);
        } elseif (!count($object)) {
            return $this->getSubField('string');
        } else {
            return $this->getSubFieldLastUsed();
        }
    }

    /**
     * @return AbstractFormField
     */
    protected function getSubFieldLastUsed()
    {
        if ($this->lastUsedType !== null) {
            return $this->getSubField($this->lastUsedType);
        } else {
            return $this->getSubField('string');
        }
    }

    /**
     * @param int $id
     * @return AbstractFormField
     */
    protected function getSubFieldByPk($id)
    {
        $key = $this->tableData['key'];
        $table = $this->tableData['table'];
        $query = \megabike\db\DbManager::createSelect($this->typeField, $table, "{$key}=".(int)$id);
        $row = \megabike\db\DbManager::queryRow($query);
        return $row ? $this->getSubField($row[$this->typeField]) : null;
    }

    public function getTextValue($object)
    {
        return $this->getSubFieldByData($object)->getTextValue($object);
    }

    public function getHtmlValue($object)
    {
        return $this->getSubFieldByData($object)->getHtmlValue($object);
    }

    public function getFullHtmlValue($object)
    {
        return $this->getSubFieldByData($object)->getFullHtmlValue($object);
    }

    public function getNormalValue($object)
    {
        return $this->getSubFieldByData($object)->getNormalValue($object);
    }

    public function isOrderField()
    {
        return false;
    }

    public function isWhereField()
    {
        return false;
    }

    public function isReadOnly()
    {
        return false;
    }

    public function getFormValue($object = null, $input = null)
    {
        return $this->getSubFieldByData($object, $input)->getFormValue($object, $input);
    }

    public function generateInputHtml($parent, $object = null, $input = null)
    {
        return $this->getSubFieldByData($object, $input)->generateInputHtml($parent, $object, $input);
    }

    protected function generateDefaultError()
    {
        return $this->getSubFieldLastUsed()->generateDefaultError();
    }

    protected function generateRequiredError()
    {
        return $this->getSubFieldLastUsed()->generateRequiredError();
    }

    protected function writeOutputValue($formValue, $object, &$output)
    {
        return $this->getSubFieldByData($object)->writeOutputValue($formValue, $object, $output);
    }

    public function validate($formValue, &$error = null)
    {
        return $this->getSubFieldLastUsed()->validate($formValue, $error);
    }
    
    public function validateOutput($object, $output, &$error = null)
    {
        return $this->getSubFieldLastUsed()->validateOutput($object, $output, $error);
    }

    public function isEmpty($formValue)
    {
        return $this->getSubFieldLastUsed()->isEmpty($formValue);
    }

    public function processInput($object, $input, &$value, &$error = null)
    {
        return $this->getSubFieldByData($object, $input)->processInput($object, $input, $value, $error);
    }

    public function writeOutput($object, $values, &$output)
    {
        return $this->getSubFieldByData($object)->writeOutput($object, $values, $output);
    }

}