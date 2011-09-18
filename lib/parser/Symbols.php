<?php

namespace phpml\lib\parser;

/**
 * Symbols class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser
 */
use phpml\lib\exception\util\ExceptionFactory;

class Symbols
{
    protected static $namespaces = array('php' => 'phpml\components');
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
    
    // TODO exception with file information
    public static function addNamespace($name, $ns)
    {
        if (array_key_exists($name, self::$namespaces))
            throw ExceptionFactory::createDuplicatedPrefix(__FILE__, __LINE__, $name, $ns);
            
        self::$namespaces[$name] = $ns;
    }
    
    public static function getRegisteredNamespaces()
    {
        return array_keys(self::$namespaces);
    }
    
    public static function getNamespace($name)
    {
        return array_key_exists($name, self::$namespaces) ? self::$namespaces[$name] : false;
    }
}