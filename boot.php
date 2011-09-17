<?php

use phpml\lib\parser\Parser;
function load($name)
{
    require '../' . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
}

spl_autoload_register('load');


use \phpml\lib\parser\File,
    \phpml\lib\parser\Scanner;

try {
    $file = new File('tests/_files/parse_file');
    $scanner = new Scanner($file);

//    while (($t = $scanner->nextToken()) != false)
//        var_dump($t);
    
    $p = new Parser($scanner);
    foreach ($p->parse() as $v)
        var_dump($v);

} catch (Exception $e) {
    echo $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine();
}
