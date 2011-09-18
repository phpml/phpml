<?php

namespace phpml\components;

class Div extends Component
{
    public function __construct()
    {
        parent::__construct(); 
    }
    
    public function __toString()
    {
        $html = '<div>';
        
        foreach ($this->childs as $child)
            $html .= $child;
            
        $html .= '</div>';
        
        return $html;
    }
}