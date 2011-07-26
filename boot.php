<?php

function __autoload($name)
{
    require str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
}


use \phpml\lib\parser\File,
    \phpml\lib\parser\Scanner;

$file = new File('tests/_files/find_1');

//var_dump(ftell($file->savedState->filePointer));
//var_dump(ftell($file->getFilePointer()));
//var_dump($file->find('<php'));
//echo $file->getCurrentPos();


$scanner = new Scanner($file);

try {

    while (($t = $scanner->nextToken()) != false)
        var_dump($t);

} catch (Exception $e) {
    echo $e->getMessage(), $e->getLine();
}
