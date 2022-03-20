<?php

namespace megabike\forms;

abstract class FieldsBuilder
{

    /**
     *
     * @var array
     */
    private $namespaces;

    /**
     * 
     */
    public function __construct()
    {
        $this->namespaces = $this->getDefaultClassesNamespaces();
    }

    /**
     * 
     * @return array
     */
    protected function getDefaultClassesNamespaces()
    {
        return FormSettings::getInstance()->getDefaultClassesNamespaces();
    }

    /**
     * 
     * @return array
     */
    public final function getClassesNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * 
     * @param string $namespace
     * @param mixed $directories
     */
    public final function addClassesNamespace($namespace, $directories = null)
    {
        if (is_array($namespace)) {
            $this->namespaces = $this->mergeClassesNamespaces($this->namespaces, $namespace);
        } else {
            if (!isset($this->namespaces[$namespace])) {
                $this->namespaces[$namespace] = $directories;
            } elseif ($directories !== null) {
                $this->namespaces[$namespace] = array_merge(array_unique(array_merge((array)$this->namespaces[$namespace], (array)$directories)));
            }
        }
    }

    /**
     * 
     * @param array $nsMap1
     * @param array $nsMap2
     * @return array
     */
    protected function mergeClassesNamespaces($nsMap1, $nsMap2)
    {
        $result = array();
        foreach ($nsMap1 as $nskey => $nsval) {
            if (is_int($nskey)) {
                $result[$nsval] = null;
            } else {
                $result[$nskey] = $nsval;
            }
        }
        foreach ($nsMap2 as $nskey => $nsval) {
            if (is_int($nskey)) {
                $namespace = $nsval;
                $directories = null;
            } else {
                $namespace = $nskey;
                $directories = $nsval;
            }
            if (!isset($result[$namespace])) {
                $result[$namespace] = $directories;
            } elseif ($directories !== null) {
                $result[$namespace] = array_merge(array_unique(array_merge((array)$result[$namespace], (array)$directories)));
            }
        }
        return $result;
    }

    /**
     * 
     * @param string $type
     * @param string $mode
     * @return string
     */
    protected function buildFieldClassShortName($type, $mode)
    {
        return ucfirst($type).ucfirst($mode);
    }

    /**
     * 
     * @param string $directory
     * @param string $classShortName
     * @return string
     */
    protected function buildClassPath($directory, $classShortName)
    {
        return rtrim($directory, '\\/').'/'.$classShortName.'.php';
    }

    /**
     * 
     * @param string $directory
     * @param string $class
     * @param string $classShortName
     * @return boolean
     */
    protected function importClassFromDirectory($directory, $class, $classShortName)
    {
        $path = $this->buildClassPath($directory, $classShortName);
        if (is_file($path)) {
            include_once($path);
            if (class_exists($class, false)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 
     * @param string $type
     * @param string $mode
     * @return string
     */
    protected function importFieldByType($type, $mode)
    {
        $classShortName = $this->buildFieldClassShortName($type, $mode);
        $namespaces = (array)$this->getClassesNamespaces();
        foreach ($namespaces as $nskey => $nsval) {
            if (is_int($nskey)) {
                $namespace = $nsval;
                $directories = null;
            } else {
                $namespace = $nskey;
                $directories = !empty($nsval) ? (array)$nsval : null;
            }
            $class = ltrim($namespace.'\\'.$classShortName, '\\');
            if ($directories !== null) {
                foreach ($directories as $directory) {
                    if ($this->importClassFromDirectory($directory, $class, $classShortName)) {
                        return $class;
                    }
                }
            }
            if (class_exists($class, true)) {
                return $class;
            }
        }
        if (class_exists($classShortName, true)) {
            return $classShortName;
        } else {
            return null;
        }
    }

    /**
     * 
     * @param string $id
     * @param array $params
     * @return array
     */
    protected function prepareFieldParams($id, $params)
    {
        if (isset($params[0]) && !isset($params['field'])) {
            $params['field'] = $params[0];
        }
        if (isset($params['field']) && $params['field'] === true) {
            $params['field'] = $id;
        }
        if (!isset($params['subset']) && isset($params['field'])) {
            $p = strpos($params['field'], '.');
            if ($p !== false) {
                $params['subset'] = substr($params['field'], 0, $p);
                $params['field'] = substr($params['field'], $p + 1);
            }
        }
        if (isset($params[1]) && !isset($params['type'])) {
            $params['type'] = $params[1];
        }
        return $params;
    }

    /**
     * 
     * @param string $id
     * @param array $params
     * @return FieldInterface
     */
    protected function createField($id, $params)
    {
        $params = $this->prepareFieldParams($id, $params);
        if (!isset($params['class']) && isset($params['type'])) {
            $class = $this->importFieldByType($params['type'], 'field');
            if ($class === null) {
                return null;
            }
        } elseif (isset($params['class'])) {
            $class = $params['class'];
            if (!class_exists($class, true)) {
                return null;
            }
        } else {
            return null;
        }
        return new $class($id, $params, $this);
    }

}
