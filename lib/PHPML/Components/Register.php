<?php

namespace PHPML\Components;

use PHPML\Parser\Symbols;

class Register extends Component
{
    public function __construct()
    {
        parent::__construct();
        
        $this->properties['ns'] = null;
        $this->properties['prefix'] = null;
        $this->allowChildren = false;
    }
    
    public function registerNS()
    {
        Symbols::addNamespace($this->properties['prefix'], $this->properties['ns']);
    }
}