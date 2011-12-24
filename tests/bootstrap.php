<?php

date_default_timezone_set('UTC');
set_include_path(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'lib' . PATH_SEPARATOR . get_include_path());

spl_autoload_register(function($className) {
    $fileParts = explode('\\', ltrim($className, '\\'));

    if (false !== strpos(end($fileParts), '_'))
        array_splice($fileParts, -1, 1, explode('_', current($fileParts)));

    require implode(DIRECTORY_SEPARATOR, $fileParts) . '.php';
});

define('FILES_DIR', __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR);