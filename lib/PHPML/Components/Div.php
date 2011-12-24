<?php

namespace PHPML\Components;

class Div extends Component
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function __toString()
    {
        $html = '<div>';
        
        foreach ($this->children as $child)
            $html .= $child;
            
        $html .= '</div>';
        
        return $html;
    }
}