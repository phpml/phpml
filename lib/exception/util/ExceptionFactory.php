<?php

namespace phpml\lib\exception\util;

use phpml\lib\exception\ParserException,
    phpml\lib\exception\IOException;

/**
 * Factory of exceptions
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
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
    
    public static function createFileDoesNotExist($_file, $_line, $file)
    {
        return new IOException($_file, $_line, sprintf('File: %s doesn\'t exist', $file));
    }
    
    public static function createUnexpectedToken($_file, $_line, $file, $line, $token)
    {
        return new ParserException($_file, $_line, sprintf('Unexpected token (%s) in %s on line %d', $token, $file, $line));
    }
}
?>
