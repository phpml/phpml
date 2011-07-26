<?php

function loadTest($name)
{
    require dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
}

spl_autoload_register('loadTest');