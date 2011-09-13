<?php

use phpml\lib\parser\Parser;
function load($name)
{
    require '../' . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
}

spl_autoload_register('load');


use \phpml\lib\parser\File,
    \phpml\lib\parser\Scanner;

abstract class Component
{
    protected $childs;
    protected $parent;
    protected $id;
    protected $properties;
    
    public function __construct()
    {
        $this->childs = array();
        $this->properties = array();
    }
    
    public function addChild($child)
    {
        $this->childs[] = $child;
    }
    
    public function setParent($parent)
    {
        $this->parent = $parent;
    }
    
    public function getParent()
    {
        return $this->parent;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getId()
    {
        return $this->id;
    }
}

class Label extends Component {

}
class Div extends Component {}
class Image extends Component {}
class Load extends Component {}
    
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
