<?php

namespace megabike\forms;

use megabike\forms\fields\AbstractField;

class FormTableButton extends AbstractField
{

    protected $keyField = null;
    protected $baseUrl = null;
    protected $baseRoute = null;
    protected $urlParams = null;
    protected $routePattern = null;
    protected $dynamicParams = null;
    protected $imageUrl = null;
    protected $cssClass = null;
    protected $confirmText = null;
    protected $enabledCallback = null;

    protected function initializeMetadata($object)
    {
        if ($object instanceof FormTableInterface) {
            if ($this->baseUrl === null) {
                $this->baseUrl = $object->getBaseUrl();
            }
        }
        if ($object instanceof DbTableProviderInterface) {
            if ($this->keyField === null) {
                $this->keyField = $object->getTableKey();
            }
        }
        parent::initializeMetadata($object);
    }

    protected function initialize()
    {
        if ($this->baseUrl) {
            $pos = strpos($this->baseUrl, '?');
            if ($pos === false) {
                $pos = strpos($this->baseUrl, '#');
            }
            if ($pos !== false) {
                $this->baseUrl = substr($this->baseUrl, 0, $pos);
            }
        }
    }

    public function isEnabledFor($object)
    {
        if ($this->enabledCallback !== null) {
            return call_user_func($this->enabledCallback, $object, $this);
        }
        return true;
    }

    public function getUrl($object)
    {
        $route = '';
        if ($this->routePattern !== null) {
            $parts = is_array($this->routePattern) ? $this->routePattern : explode('/', trim($this->routePattern, '/'));
            foreach ($parts as $part) {
                $p = $this->applyRoutePattern($part, $object);
                if ($p !== false) {
                    $route .= $p.'/';
                } else {
                    return false;
                }
            }
        }

        $defaultParams = array();
        if ($this->urlParams !== null) {
            if (is_array($this->urlParams)) {
                $defaultParams = $this->urlParams;
            } else {
                parse_str($this->urlParams, $defaultParams);
            }
        }

        $params = $this->applyDynamicParams($defaultParams, $object);
        if ((string)$this->baseRoute !== '') {
            $url = $this->buildUrlToRoute($this->baseRoute, $route, $params);
            if ((string)$url !== '') {
                return $url;
            }
        }
        return $this->buildUrl($this->baseUrl, $route, $params);
    }

    protected function buildUrlToRoute($baseRoute, $route, $params)
    {
        $r = trim($baseRoute, '/');
        $r .= '/'.ltrim($route, '/');
        $url = '/'.ltrim($r, '/');
        if ($params) {
            $query = (string)http_build_query($params);
            if ($query !== '') {
                $url .= '?'.$query;
            }
        }
        return $url;
    }

    protected function buildUrl($baseUrl, $route, $params)
    {
        if ((string)$baseUrl !== '') {
            $url = rtrim($baseUrl, '/').'/';
        } else {
            $url = '/';
        }
        if ((string)$route !== '') {
            $url .= ltrim($route, '/');
        }
        if ($params) {
            $query = (string)http_build_query($params);
            if ($query !== '') {
                $url .= '?'.$query;
            }
        }
        return $url;
    }

    protected function applyRoutePattern($pattern, $object)
    {
        if ($pattern === '#') {
            if ($this->keyField !== null && isset($object[$this->keyField])) {
                return (string)$object[$this->keyField];
            } else {
                return false;
            }
        }
        return $pattern;
    }

    protected function applyDynamicParams($params, $object)
    {
        if ($this->dynamicParams) {
            foreach ($this->dynamicParams as $pkey => $okey) {
                if ($okey === '#' && $this->keyField !== null && isset($object[$this->keyField])) {
                    $params[$pkey] = (string)$object[$this->keyField];
                } elseif (isset($object[$okey])) {
                    $params[$pkey] = $object[$okey];
                }
            }
        }
        return $params;
    }

    public function getStyle($isFull = false)
    {
        $style = '';
        if ($this->imageUrl) {
            $image = $this->imageUrl;
            $style .= 'background-image:url(\''.addslashes($image).'\');';
        }
        return $style;
    }

    public function getCssClass($isFull = false)
    {
        $cssClass = $this->cssClass;
        if (!$this->imageUrl) {
            $cssClass .= ' noimg';
        }
        return trim($cssClass);
    }

    public final function getConfirmText()
    {
        return $this->confirmText;
    }

    public final function getNormalValue($object)
    {
        return $this->getUrl($object);
    }

    public final function getTextValue($object)
    {
        return $this->getUrl($object);
    }

    public function getHtmlValue($object)
    {
        $url = $this->getUrl($object);
        $style = $this->getStyle(false);
        $cssClass = $this->getCssClass(false);

        $buffer = '';
        $buffer .= '<div class="'.$cssClass.'">';
        $buffer .= '<a href="'.html_encode($url).'" title="'.html_encode($this->getName()).'"';
        if ($style) {
            $buffer .= ' style="'.html_encode($style).'"';
        }
        if ($this->confirmText) {
            $buffer .= ' onclick="return confirm(\''.addslashes($this->confirmText).'\')"';
        }
        $buffer .= '></a>';
        $buffer .= '</div>';
        return $buffer;
    }

    public function getFullHtmlValue($object)
    {
        $url = $this->getUrl($object);
        $style = $this->getStyle(true);
        $cssClass = $this->getCssClass(true);

        $buffer = '';
        $buffer .= '<div class="'.$cssClass.'">';
        $buffer .= '<a href="'.html_encode($url).'" title="'.html_encode($this->getName()).'"';
        if ($style) {
            $buffer .= ' style="'.html_encode($style).'"';
        }
        if ($this->confirmText) {
            $buffer .= ' onclick="return confirm(\''.addslashes($this->confirmText).'\')"';
        }
        $buffer .= '>'.html_encode($this->getName()).'</a>';
        $buffer .= '</div>';
        return $buffer;
    }

    public final function getWhereOperators()
    {
        return null;
    }

    public final function getWhereCondition($value, $operator, $tableAlias = '')
    {
        return null;
    }

    public final function getOrderExpression($order, $tableAlias = '')
    {
        return null;
    }

    public final function getOrderDefaultDirection()
    {
        return null;
    }

    public final function isRealField()
    {
        return false;
    }

    public final function isOrderField()
    {
        return false;
    }

    public final function isWhereField()
    {
        return false;
    }

}
