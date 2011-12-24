<?php

namespace PHPML\Parser\Token;

/**
 * SimpleToken class
 *
 * T_TEXT, T_VALUE, T_ATTRIBUTE
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser.token
 */
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
