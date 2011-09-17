<?php

namespace phpml\components;

class Register extends Component
{
    public function __construct()
    {
        parent::__construct();
        
        $this->properties['ns'] = null;
        $this->properties['prefix'] = null;
    }
}