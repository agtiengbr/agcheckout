<?php

namespace AGTI\Checkout\Entity;

use JsonSerializable;

abstract class Field implements JsonSerializable
{
    protected $name;
    protected $id;
    protected $required;

    protected $colXl;
    protected $colLg;
    protected $colMd;
    protected $colSm;
    protected $isVanilla;

    /**
     * Get the value of colSm
     */ 
    public function getColSm()
    {
        return $this->colSm;
    }

    /**
     * Set the value of colSm
     *
     * @return  self
     */ 
    public function setColSm($colSm)
    {
        $this->colSm = $colSm;

        return $this;
    }

    /**
     * Get the value of colMd
     */ 
    public function getColMd()
    {
        return $this->colMd;
    }

    /**
     * Set the value of colMd
     *
     * @return  self
     */ 
    public function setColMd($colMd)
    {
        $this->colMd = $colMd;

        return $this;
    }

    /**
     * Get the value of colLg
     */ 
    public function getColLg()
    {
        return $this->colLg;
    }

    /**
     * Set the value of colLg
     *
     * @return  self
     */ 
    public function setColLg($colLg)
    {
        $this->colLg = $colLg;

        return $this;
    }

    /**
     * Get the value of colXl
     */ 
    public function getColXl()
    {
        return $this->colXl;
    }

    /**
     * Set the value of colXl
     *
     * @return  self
     */ 
    public function setColXl($colXl)
    {
        $this->colXl = $colXl;

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
     * Get the value of isVanilla
     */ 
    public function getIsVanilla()
    {
        return $this->isVanilla;
    }

    /**
     * Set the value of isVanilla
     *
     * @return  self
     */ 
    public function setIsVanilla($isVanilla)
    {
        $this->isVanilla = $isVanilla;

        return $this;
    }

    /**
     * Get the value of required
     */ 
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Set the value of required
     *
     * @return  self
     */ 
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->getName(),
            'id' => $this->getId(),
            'required' => $this->getRequired()
        ];
    }
}