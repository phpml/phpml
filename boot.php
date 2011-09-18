<?php

use phpml\components\Label;
use phpml\lib\PHPML;
use phpml\lib\parser\Compiler;
use phpml\lib\parser\Parser;
function load($name)
{
    require '../' . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
}

spl_autoload_register('load');


use \phpml\lib\parser\File,
    \phpml\lib\parser\Scanner;

try {
//    $c = new Compiler('tests/_files/parse_file');
//    
//    foreach ($c->compile() as $comp)
//        var_dump($comp);

    $tree = PHPML::getInstance()->loadTemplate('tests/_files/first_page.pml');
    
    $label = new Label();
    $label->value = 'Thiago';
    $tree[1]->addChild($label);
    
    foreach ($tree as $t)
        echo $t;
        
} catch (Exception $e) {
    echo $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine();
}
