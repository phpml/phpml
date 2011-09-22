<?php

namespace phpml\components;

use phpml\lib\exception\util\ExceptionFactory;

abstract class Component
{
    protected $childs;
    protected $parent;
    protected $id;
    protected $properties;

    public function __construct()
    {
        $this->childs = array();
        $this->properties = array();
    }

    public function addChild($child)
    {
        $this->childs[] = $child;
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

    public function getTag()
    {
        return strtolower(end(explode('\\', get_called_class())));
    }

    public function __set($prop, $value)
    {
        // Does this property exist?
        if (array_key_exists($prop, $this->properties))
            $this->properties[$prop] = $value;
        else
            throw ExceptionFactory::createSetUnexpectedProperty(
                    __FILE__, __LINE__, $this, $prop
            );
    }

    public function __get($prop)
    {
        // Does this property exist?
        if (array_key_exists($prop, $this->properties))
            return $this->properties[$prop];
        else
            throw ExceptionFactory::createGetUnexpectedProperty(
                    __FILE__, __LINE__, $this, $prop
            );
    }

    public function __toString()
    {
        $html = '<' . $this->getTag() . '>';

        foreach ($this->childs as $child)
            $html .= $child;

        $html .= '</' . $this->getTag() . '>';

        return $html;
    }

}