<?php

namespace phpml\lib\parser;

use phpml\lib\parser\token\SimpleToken,
    phpml\lib\parser\token\TagToken,
    phpml\lib\parser\token\Token;

/**
 * ComponentBuilder class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser
 */
class ComponentBuilder
{
    protected $openTag;
    protected $attributes;
    protected $values;
    
    public function __construct()
    {
        $this->attributes = array();
        $this->values = array();
    }
    
    public function setOpenTag(TagToken $openTag)
    {
        $this->openTag = $openTag;
    }
    
    public function addAttr(SimpleToken $attr)
    {
        $this->attributes[] = $attr;
    }
    
    public function addValue(SimpleToken $value)
    {
        $this->values[] = $value;
    }
    
    protected function buildOpenTag()
    {
        $className = $this->openTag->getName();
        return new $className();
    }
    
    protected function cleanUp()
    {
        $this->openTag = null;
        $this->attributes = array();
        $this->values = array();
    }
    
    public function build()
    {
        // Instantiate the component
        $component = $this->buildOpenTag();
        
        foreach ($this->attributes as $key => $attr)
            $component->{$attr->getValue()} = $this->values[$key]->getValue();
            
        // Clean up the old parameters
        $this->cleanUp();
            
        return $component;
    }
}