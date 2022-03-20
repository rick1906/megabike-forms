<?php

namespace megabike\forms;

class BufferedDataReader extends DbDataReader
{

    /**
     * 
     * @var BufferedCriteriaManager
     */
    protected $manager;

    /**
     * 
     * @var mixed
     */
    protected $buffer;

    /**
     * 
     * @param BufferedCriteriaManager $bufferedCriteriaManager
     */
    public function __construct($bufferedCriteriaManager)
    {
        $this->manager = $bufferedCriteriaManager;
        $this->buffer = null;
        parent::__construct('');
    }

    public function next()
    {
        if ($this->buffer === null) {
            $this->buffer = $this->manager->getBuffer();
        }

        $this->index++;
        if (isset($this->buffer[$this->index])) {
            $this->current = $this->buffer[$this->index];
            if (!is_object($this->current)) {
                $this->current = (array)$this->current;
            }
        } else {
            $this->current = false;
        }
    }

    public function count()
    {
        if ($this->buffer === null) {
            $this->buffer = $this->manager->getBuffer();
        }
        return count($this->buffer);
    }

}
