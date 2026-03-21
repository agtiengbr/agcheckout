<?php

namespace AGTI\Checkout\Entity;

use JsonSerializable;

class PersonType implements JsonSerializable
{
    protected $name;
    protected $id;
    protected $active;
    protected $fields;
    /** @var \Module */
    protected $module;

    public function __construct(\Module $module)
    {
        $this->module = $module;
    }

    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of fields
     */ 
    public function getFields()
    {
        if (is_array($this->fields)) {
            return $this->fields;
        }
    }

    /**
     * Set the value of fields
     *
     * @return  self
     */ 
    public function addField($field)
    {
        $this->fields[] = $field;
        return $this;
    }

    /**
     * Get the value of active
     */ 
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set the value of active
     *
     * @return  self
     */ 
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get the value of module
     */ 
    public function getModule()
    {
        return $this->module;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'active' => $this->getActive(),
            'name' => $this->getName(),
            'fields' => $this->getFields(),
            'module' => $this->getModule()
        ];
    }
}