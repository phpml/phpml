<?php

use phpml\lib\parser\Parser;
function load($name)
{
    require '../' . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
}

spl_autoload_register('load');


use \phpml\lib\parser\File,
    \phpml\lib\parser\Scanner;

//var_dump(ftell($file->savedState->filePointer));
//var_dump(ftell($file->getFilePointer()));
//var_dump($file->find('<php'));
//echo $file->getCurrentPos();


try {
    $file = new File('tests/_files/find_1');
    $scanner = new Scanner($file);

//    while (($t = $scanner->nextToken()) != false)
//        var_dump($t);

    $p = new Parser($scanner);
    var_dump($p->paser());

} catch (Exception $e) {
    echo $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine();
}

