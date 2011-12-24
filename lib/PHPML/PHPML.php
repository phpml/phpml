<?php

namespace PHPML;

use PHPML\Parser\Compiler;

class PHPML
{
    private static $instance = null;
    
    private function __construct() {}
    
    public static function getInstance()
    {
        if (is_null(self::$instance))
            self::$instance = new self();
            
        return self::$instance;
    }
    
    public function loadTemplate($pathToFile)
    {
        $c = new Compiler($pathToFile);
        return $c->compile();
    }
}