<?php

namespace PHPML\Parser;

/**
 * Compiler class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser
 */
class Compiler
{
    protected $pathToFile;
    
    public function __construct($pathToFile)
    {
        $this->pathToFile = $pathToFile;
    }
    
    public function compile()
    {
        $parser = new Parser(new Scanner(new File($this->pathToFile)));
        return $parser->parse();
    }
}