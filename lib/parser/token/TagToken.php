<?php

namespace phpml\lib\parser\token;

/**
 * Description of TagToken
 *
 * @author Thiago
 */
class TagToken extends Token
{
    protected $namespace;
    protected $name;

    public function __construct($type, $namespace, $name)
    {
        parent::__construct($type);
        $this->namespace = $namespace;
        $this->name      = $name;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getName()
    {
        return $this->name;
    }
}
