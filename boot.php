<?php

function __autoload($name)
{
    require str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
}


use phpml\parser\File,
    phpml\parser\Scanner;

$file = new File('tests/testFiles/find_1');
//var_dump($file->find('<php'));
//echo $file->getCurrentPos();


$scanner = new Scanner($file);

var_dump($scanner->nextToken());
var_dump($scanner->nextToken());

echo 'hahah';