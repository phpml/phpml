<?php

namespace phpml\lib\parser;

/**
 * Tree class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser
 */
class Tree extends \SplDoublyLinkedList
{
    protected $top;
    
    public function push($value, $parent = null)
    {
        if ($parent)
            $parent->addChild($value);
        else            
            parent::push($value);
            
        // We don't want T_TEXT
        if (gettype($value) == 'object')
            $this->top = $value;
    }
    
    // TODO throw an exception for empty tree
    public function top()
    {
        return $this->top;
    }
}