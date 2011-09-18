<?php

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
    $c = new Compiler('tests/_files/parse_file');
    var_dump($c->compile());
} catch (Exception $e) {
    echo $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine();
}
