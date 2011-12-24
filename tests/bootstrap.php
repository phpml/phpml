<?php

set_include_path(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'lib' . PATH_SEPARATOR . get_include_path());

/**
 * Autoloader that implements the PSR-0 spec for interoperability between
 * PHP software.
 * 
 * Got it from github.com/Respect
 */
spl_autoload_register(
    function($className) {
        $fileParts = explode('\\', ltrim($className, '\\'));

        if (false !== strpos(end($fileParts), '_'))
            array_splice($fileParts, -1, 1, explode('_', current($fileParts)));

        $file = implode(DIRECTORY_SEPARATOR, $fileParts) . '.php';

        foreach (explode(PATH_SEPARATOR, get_include_path()) as $path) {
            if (file_exists($path = $path . DIRECTORY_SEPARATOR . $file))
                return require $path;
        }
    }
);

define('FILES_DIR', __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR);