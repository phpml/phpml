<?php

namespace phpml\components\cp;

use phpml\components\Component;

class Image extends Component
{
    public function __construct()
    {
        parent::__construct();
        $this->properties['src'] = null;
    }
    
    public function __toString()
    {
        return "<img src=\"{$this->properties['src']}\" />";
        
    }
}