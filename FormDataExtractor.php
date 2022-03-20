<?php

/*
 * Copyright (c) 2021 Rick
 *
 * This file is a part of a project 'megabike'.
 * MIT licence.
 */

namespace megabike\forms;

/**
 *
 * @author Rick
 */
class FormDataExtractor
{

    public $subsetCallbacks = [];
    public $subsetModelCallbacks = [];
    public $subsetNameCallbacks = [];
    public $modelCallback = null;
    public $nameCallback = null;

    public function __construct($modelCallback = null, $nameCallback = null)
    {
        $this->modelCallback = $modelCallback;
        $this->nameCallback = $nameCallback;
    }

    public function forSubset($key)
    {
        $modelCallback = isset($this->subsetModelCallbacks[$key]) ? $this->subsetModelCallbacks[$key] : null;
        $nameCallback = isset($this->subsetNameCallbacks[$key]) ? $this->subsetNameCallbacks[$key] : null;
        return new FormDataExtractor($modelCallback, $nameCallback);
    }

    public function extractName($subset)
    {
        $key = (string)$subset->getSubsetId();
        if ($key !== '') {
            return $this->extractSubsetName($subset, $key);
        } elseif ($this->nameCallback !== null) {
            return call_user_func($this->nameCallback, $subset);
        } else {
            return null;
        }
    }

    public function createModel($subset)
    {
        $key = (string)$subset->getSubsetId();
        if ($key !== '') {
            return $this->createSubsetModel($subset, $key);
        } elseif ($this->modelCallback !== null) {
            return call_user_func($this->modelCallback, $subset);
        } else {
            return null;
        }
    }

    public function extractSubsetName($subset, $key)
    {
        if (isset($this->subsetNameCallbacks[$key])) {
            return call_user_func($this->subsetNameCallbacks[$key], $subset);
        }
        return null;
    }

    public function createSubsetModel($subset, $key)
    {
        if (isset($this->subsetModelCallbacks[$key])) {
            return call_user_func($this->subsetModelCallbacks[$key], $subset);
        }
        return null;
    }

    public function getSubsetData($data, $key)
    {
        if (isset($this->subsetCallbacks[$key])) {
            return call_user_func($this->subsetCallbacks[$key], $data);
        }
        return null;
    }

    public function hasSubsetData($data, $key)
    {
        if (isset($this->subsetCallbacks[$key])) {
            return true;
        }
        return false;
    }

}
