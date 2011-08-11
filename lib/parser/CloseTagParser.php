<?php

namespace phpml\lib\parser;

/**
 * CloseTagParser class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser
 */
class CloseTagParser implements TokenParser
{
    public static function parse(Scanner $scanner);
}
