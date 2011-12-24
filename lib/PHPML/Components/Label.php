<?php

namespace PHPML\Components;

class Label extends Component
{
    public function __construct()
    {
        parent::__construct();
        
        $this->properties['value'] = null;
    }
    
    public function __toString()
    {
        return "<p>{$this->properties['value']}</p>";
    }
}