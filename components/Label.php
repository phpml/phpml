<?php

namespace phpml\components;

class Label extends Component
{
    public function __construct()
    {
        parent::__construct();

        $this->properties['value'] = null;
    }
}