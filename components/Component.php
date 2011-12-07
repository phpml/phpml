<?php

namespace phpml\components;

use phpml\lib\exception\util\ExceptionFactory;

abstract class Component
{
    protected $allowChildren = true;
    protected $children;
    protected $parent;
    protected $id;
    protected $properties;
    
    public function __construct()
    {
        $this->children = array();
        $this->properties = array();
    }
    
    public function isChildrenAllowed()
    {
        return $this->allowChildren;
    }
    
    public function addChild($child)
    {
        if (!$this->allowChildren)
            throw ExceptionFactory::createChildrenNotAllowed(__FILE__, __LINE__, $this->getId());
        
        $this->children[] = $child;
    }
    
    public function setParent($parent)
    {
        $this->parent = $parent;
    }
    
    public function getParent()
    {
        return $this->parent;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function __set($prop, $value)
    {
        // Does this property exist?
        if (array_key_exists($prop, $this->properties))
            $this->properties[$prop] = $value;
        else
            throw ExceptionFactory::createSetUnexpectedProperty(
                __FILE__,
                __LINE__,
                $this,
                $prop
            );
    }
    
    public function __get($prop)
    {
        // Does this property exist?
        if (array_key_exists($prop, $this->properties))
            return $this->properties[$prop];
        else
            throw ExceptionFactory::createGetUnexpectedProperty(
                __FILE__,
                __LINE__,
                $this,
                $prop
            );
    }
}