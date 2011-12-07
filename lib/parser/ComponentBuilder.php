<?php

namespace phpml\lib\parser;

use phpml\lib\exception\util\ExceptionFactory;

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
    protected $file;
    
    public function __construct(File $file)
    {
        $this->attributes = array();
        $this->values = array();
        $this->file = $file;
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
    // TODO load with namespace from the components directory
    // TODO verify instanceof
    protected function buildOpenTag()
    {
        $ns = Symbols::getNamespace($this->openTag->getNamespace());
        
        // Namespace must be set
        if ($ns === false)
            throw ExceptionFactory::createInvalidNamespace(
                __FILE__,
                __LINE__,
                $this->file->getFileName(),
                $this->file->getCurrentLine(),
                $this->openTag->getNamespace()
            );
        
        $className = $ns . '\\' . $this->openTag->getName();
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
        
        foreach ($this->attributes as $key => $attr) {
            
            // Id must be a unique T_ATTRIBUTE
            if ($attr->getValue() == 'id') {
                
                // If component's id is not set yet
                if (is_null($component->getId())) {
                    $component->setId($this->values[$key]->getValue());
                    
                    // Add the id into the Symbols Table
                    if (!Symbols::addId($component->getId(), $component))
                        throw ExceptionFactory::createDuplicatedId(
                            __FILE__,
                            __LINE__,
                            $this->file->getFileName(),
                            $this->file->getCurrentLine(),
                            $component->getId()
                        );
                    
                } else {
                    
                    // Duplicated T_ATTRIBUTE id
                    throw ExceptionFactory::createDuplicatedTagId(
                        __FILE__,
                        __LINE__,
                        $this->file->getFileName(),
                        $this->file->getCurrentLine()
                    );
                }
                
                // It's not an id
                continue;
            }
            
            // Set other properties
            $component->{$attr->getValue()} = $this->values[$key]->getValue();
        }
        
        // Clean up the old parameters
        $this->cleanUp();
            
        return $component;
    }
}