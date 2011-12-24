<?php

set_include_path(__DIR__ . DIRECTORY_SEPARATOR . 'lib' . PATH_SEPARATOR . get_include_path());

spl_autoload_register(function($className) {
    $fileParts = explode('\\', ltrim($className, '\\'));

    if (false !== strpos(end($fileParts), '_'))
        array_splice($fileParts, -1, 1, explode('_', current($fileParts)));

    require implode(DIRECTORY_SEPARATOR, $fileParts) . '.php';
});

try {

    $tree = PHPML\PHPML::getInstance()->loadTemplate('tests/_files/first_page.pml');
    
    $label = new PHPML\Components\Label();
    $label->value = 'Thiago';
    $tree->getElementById('ha')->addChild($label);
    $tree->getElementById('img')->src = 'https://www.google.com/logos/classicplus.png';
    
    echo $tree;
        
} catch (Exception $e) {
    echo $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine();
}
