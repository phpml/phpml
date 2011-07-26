<?php

namespace phpml\lib\parser\token;

class SimpleToken extends Token
{
    protected $value;

    public function __construct($type, $value)
    {
        parent::__construct($type);
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}
