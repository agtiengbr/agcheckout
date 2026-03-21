<?php

namespace AGTI\Checkout\Entity;

use AGTI\Checkout\Exception\InconsistentFieldType;

class Form
{
    /** @var Row[] */
    protected $rows;

    protected $type;
    
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Get the value of rows
     */ 
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Set the value of rows
     *
     * @return  self
     */ 
    public function addRow(Row $row)
    {
        if ($this->getType() != $row->getType()) {
            throw new InconsistentFieldType("A linha inserida é do tipo " . $row->getType() . " mas esse formulário é do tipo " . $this->getType() . ".");
        }

        $this->rows[] = $row;

        return $this;
    }

    /**
     * Get the value of type
     */ 
    public function getType()
    {
        return $this->type;
    }
}