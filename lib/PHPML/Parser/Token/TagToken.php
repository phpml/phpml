<?php

namespace PHPML\Parser\Token;

/**
 * TagToken class
 *
 * T_OPEN_TAG, T_CLOSE_TAG
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser.token
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
