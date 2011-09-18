<?php

namespace phpml\lib;

use phpml\lib\parser\Compiler;

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