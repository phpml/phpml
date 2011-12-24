<?php

namespace PHPML\Parser;

/**
 * TokenParser interface
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser
 */
interface TokenParser
{
    public static function parse(Scanner $scanner);
}
