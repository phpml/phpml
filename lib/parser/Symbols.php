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
// FIXME I have to remove most of this exceptions and behaviours, because they don't belong to this class
class Symbols
{
    protected static $namespaces = array('php' => 'phpml\components');
    protected static $ids = array();
    
    public static function addId($id, $component)
    {
        if (self::idExists($id))
            return false;
            
        self::$ids[$id] = $component;
        
        return true;
    }
    
    public static function getComponentById($id)
    {
        if (!self::idExists($id))
            throw ExceptionFactory::createUndefinedId(__FILE__, __LINE__, $id);
            
        return self::$ids[$id];
    }
    
    public static function idExists($id)
    {
        return isset(self::$ids[$id]);
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