<?php

namespace phpml\lib\parser;

/**
 * EndParser class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser
 */
class EndParser implements TokenParser
{
    public static function parse(Scanner $scanner);
}
