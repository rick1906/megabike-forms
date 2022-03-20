<?php

namespace megabike\forms;

use Countable;
use Exception;
use Iterator;

class DbDataReader implements Iterator, Countable
{
    protected $dbQuery;
    protected $dbResult;
    protected $transformFunction;
    protected $current;
    protected $index;

    public function __construct($dbResultOrQuery)
    {
        if (is_string($dbResultOrQuery)) {
            $this->dbQuery = $dbResultOrQuery;
            $this->dbResult = null;
        } else {
            $this->dbQuery = null;
            $this->dbResult = $dbResultOrQuery;
        }
        $this->transformFunction = null;
        $this->current = null;
        $this->index = -1;
    }

    public function setTransformFonction($function)
    {
        $this->transformFunction = $function;
    }

    protected function transfrom($row)
    {
        if (empty($row)) {
            return null;
        } else {
            return call_user_func($this->transformFunction, $row);
        }
    }

    public function current()
    {
        if ($this->index < 0) {
            $this->next();
        }
        if (!$this->current) {
            return null;
        }
        if ($this->transformFunction !== null) {
            return $this->transfrom($this->current);
        } else {
            return $this->current;
        }
    }

    public function key()
    {
        if ($this->index < 0) {
            $this->next();
        }
        return $this->index;
    }

    public function valid()
    {
        return $this->current !== false;
    }

    public function next()
    {
        if ($this->dbResult === null && $this->dbQuery !== null) {
            $this->dbResult = \megabike\db\DbManager::query($this->dbQuery);
            $this->dbQuery = null;
        }
        $row = \megabike\db\DbManager::fetch($this->dbResult);
        $this->current = $row ? $row : false;
        $this->index++;
    }

    public function rewind()
    {
        if ($this->index < 0) {
            $this->next();
        } else {
            throw new DbDataReaderException('Impossible to rewind '.get_class($this));
        }
    }

    public function getAll()
    {
        $all = array();
        foreach ($this as $data) {
            $all[] = $data;
        }
        return $all;
    }

    public function fetch()
    {
        $this->next();
        return $this->current();
    }

    public function count()
    {
        if ($this->dbResult === null && $this->dbQuery !== null) {
            $this->dbResult = \megabike\db\DbManager::query($this->dbQuery);
            $this->dbQuery = null;
        }
        return \megabike\db\DbManager::numRows($this->dbResult);
    }

}

class DbDataReaderException extends Exception
{

    public function __construct($message, $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}