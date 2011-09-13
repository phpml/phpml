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
    protected static $namespaces = array();
    protected static $ids = array();
    
    public static function addId($id)
    {
        if (self::idExists($id))
            return false;
            
        self::$ids[] = $id;
        
        return true;
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