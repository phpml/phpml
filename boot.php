<?php

use phpml\lib\parser\Symbols;
use phpml\components\Label;
use phpml\lib\PHPML;
use phpml\lib\parser\Compiler;
use phpml\lib\parser\Parser;
use \phpml\lib\parser\File,
    \phpml\lib\parser\Scanner;

spl_autoload_register(function ($name) {
    require '../' . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
});
    
try {

    $tree = PHPML::getInstance()->loadTemplate('tests/_files/first_page.pml');
    
    $label = new Label();
    $label->value = 'Thiago';
    $tree->getElementById('ha')->addChild($label);
    $tree->getElementById('img')->src = 'https://www.google.com/logos/classicplus.png';
    
    echo $tree;
        
} catch (Exception $e) {
    echo $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine();
}
