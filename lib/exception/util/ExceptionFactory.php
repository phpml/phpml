<?php

namespace phpml\lib\exception\util;

use phpml\lib\exception\RuntimeException;

use phpml\lib\exception\InvalidArgumentException;

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
    
    public static function createTagNotClosed($_file, $_line, $file, $tag)
    {
        return new ParserException($_file, $_line, 
                sprintf('Tag: (%s:%s) isn\'t closed properly in %s', 
                    $tag->getNamespace(), 
                    $tag->getName(), 
                    $file
                ));
    }
    
    public static function createDuplicatedTagId($_file, $_line, $file, $line)
    {
        return new ParserException($_file, $_line, 
                sprintf('Duplicated T_ATTRIBUTE id found in %s on line %d',
                    $file,
                    $line
                ));
    }
    
    public static function createDuplicatedId($_file, $_line, $file, $line, $id)
    {
        return new ParserException($_file, $_line, 
                sprintf('T_ATTRIBUTE id(%s) found in %s on line %d must be UNIQUE',
                    $id,
                    $file,
                    $line
                ));
    }
    
    public static function createSetUnexpectedProperty($_file, $_line, $component, $property)
    {
        return new InvalidArgumentException($_file, $_line, 
            sprintf('Trying to set an invalid property: %s::%s', 
                get_class($component),
                $property
            ));
    }
    
    public static function createGetUnexpectedProperty($_file, $_line, $component, $property)
    {
        return new InvalidArgumentException($_file, $_line, 
            sprintf('Trying to get an invalid property: %s::%s', 
                get_class($component),
                $property
            ));
    }
    
    public static function createInvalidNamespace($_file, $_line, $file, $line, $ns)
    {
        return new ParserException($_file, $_line, 
            sprintf('Trying to use invalid namespace(%s) in %s on line %d',
                $ns,
                $file,
                $line
            ));
    }
    
    public static function createNoChildsException($_file, $_line, $file, $line, $component, $ns)
    {
        return new ParserException($_file, $_line, 
            sprintf('Component %s:%s in %s on line %d cannot have childs',
                $ns,
                $component,
                $file,
                $line
            ));
    }
    
    public static function createDuplicatedPrefix($_file, $_line, $prefix, $ns)
    {
        return new ParserException($_file, $_line, sprintf('Prefix %s is already set with value %s', $prefix, $ns));
    }
    
    public static function createUndefinedId($_file, $_line, $id)
    {
        return new InvalidArgumentException($_file, $_line, sprintf('Undefined id %s', $id));
    }
    
    public static function createChildrenNotAllowed($_file, $_line, $id)
    {
        return new RuntimeException($_file, $_line, sprintf('Children not allowed for component with id %s', $id));
    }
}
