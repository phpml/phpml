<?php

namespace phpml\lib\parser;

/**
 * Symbols class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser
 */
class Symbols
{
    protected static $namespaces;
    protected static $ids;
    
    public static function addId($id)
    {
        self::$ids[] = $id;
    }
    
    public static function idExists($id)
    {
        return in_array($id, self::$ids);
    }
    
    public static function addNamespace($ns)
    {
        self::$namespaces[] = $ns;
    }
    
    public static function NamespaceExists($ns)
    {
        return in_array($ns, self::$namespaces);
    }
}