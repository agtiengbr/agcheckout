<?php

namespace AGTI\Checkout\Entity;

use AGTI\Checkout\Enum\FormType;
use AGTI\Checkout\Exception\InconsistentFieldType;

class Row
{
    /** @var Field[] */
    protected $fields;

    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Get the value of fields
     */ 
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Set the value of fields
     *
     * @return  self
     */ 
    public function addField(Field $field)
    {
        if (
            ($this->getType() == FormType::FORM_TYPE_CUSTOMER && !$field instanceof CustomerField)
            || ($this->getType() == FormType::FORM_TYPE_ADDRESS && !$field instanceof AddressField)
        ) {
            throw new InconsistentFieldType("O campo inserido da classe " . get_class($field) . " é inconsistente com essa linha, que é do tipo " . $this->getType() . ".");
        }

        $this->fields[] = $field;

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