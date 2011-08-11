<?php

namespace phpml\lib\parser;

/**
 * ValueParser class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser
 */
class ValueParser implements TokenParser
{
    public static function parse(Scanner $scanner);
}