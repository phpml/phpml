<?php

namespace phpml\lib\parser;

/**
 * Tree class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser
 */
use phpml\lib\parser\token\SimpleToken;

class Tree extends \SplDoublyLinkedList
{
    protected $top;
    
    public function add($component, $parent = null)
    {
        if ($parent) {
            $component->setParent($parent);
            $parent->addChild($component);
        } else {
            parent::push($component);
        }
        
        $this->top = $component;
    }
    // TODO type hinting
    public function addNoChild($component, $parent = null)
    {
        if ($parent) {
            $component->setParent($parent);
            $parent->addChild($component);
        } else {
            parent::push($component);
        }
    }
    
    public function addText(SimpleToken $text, $parent = null)
    {
        if ($parent)
            $parent->addChild($text->getValue());
        else
            parent::push($text->getValue());
    }
    
    // TODO throw an exception for empty tree
    public function top()
    {
        return $this->top;
    }
    
    public function setTop($component)
    {
        $this->top = $component;
    }
}