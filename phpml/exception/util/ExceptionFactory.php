<?php

namespace phpml\exception\util;

use phpml\exception\ParserException,
    phpml\exception\IOException;

/**
 * Factory of exceptions
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package phpml
 * @subpackage exception.util
 */
class ExceptionFactory {

    public static function createUnexpectedChar($_file, $_line, $file, $line, $char)
    {
        return new ParserException($_file, $_line, sprintf('Unexpected char %s in %s on line %d', $char, $file, $line));
    }

    public static function createCannotFindChar($_file, $_line, $file, $line, $char)
    {
        return new ParserException($_file, $_line, sprintf('Cannot find char %s in %s on line %d', $char, $file, $line));
    }

    public static function createIllegalSpace($_file, $_line, $file, $line)
    {
        return new ParserException($_file, $_line, sprintf('Illegal space found in %s on line %d', $file, $line));
    }

    public static function createOpenFile($_file, $_line, $file, $mode)
    {
        return new IOException($_file, $_line, sprintf('Cannot open file %s for %s', $file, $mode));
    }

    public static function createUnexpectedEOF($_file, $_line, $file, $line)
    {
        return new ParserException($_file, $_line, sprintf('Unexpected end of file in %s on line %d', $file, $line));
    }
}
?>
