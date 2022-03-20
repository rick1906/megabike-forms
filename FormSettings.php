<?php

/*
 * Copyright (c) 2021 Rick
 *
 * This file is a part of a project 'megabike'.
 * MIT licence.
 */

namespace megabike\forms;

use InvalidArgumentException;

/**
 *
 * @author Rick
 */
class FormSettings
{

    private static $_instance = null;
    private static $_class = null;

    /**
     * 
     * @return self
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            $class = self::$_class !== null ? self::$_class : __CLASS__;
            self::$_instance = new $class();
        }
        return self::$_instance;
    }

    public static function setInstance($value)
    {
        if ($value === null) {
            self::$_instance = null;
        } elseif ($value instanceof FormSettings) {
            self::$_instance = $value;
        } elseif (is_a((string)$value, __CLASS__, true)) {
            self::$_class = (string)$value;
        } else {
            throw new InvalidArgumentException();
        }
    }

    public function getCharset()
    {
        return 'utf-8';
    }

    public function getTableAndKeyFromModelClass($class)
    {
        return null;
    }
    
    public function getDefaultClassesNamespaces()
    {
        return array(__NAMESPACE__.'\\fields' => dirname(__FILE__).'/fields');
    }

}
