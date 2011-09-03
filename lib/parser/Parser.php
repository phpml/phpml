<?php

namespace phpml\lib\parser;

/**
 * Parser class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser
 */
use phpml\lib\parser\token\Token;

class Parser
{
    protected $stack;
    protected $tree;
    protected $scanner;
    protected $componentBuilder;
    
    public function __construct(Scanner $scanner)
    {
        $this->scanner = $scanner;
        $this->stack = new \SplStack();
        $this->componentBuilder = new ComponentBuilder();
        $this->tree = new Tree();
    }
    
    public function paser()
    {
        $token = null;
        
        while (($token = $this->scanner->nextToken()) != false) {
            
            switch ($token->getType()) {
                
                case Token::T_OPEN_TAG:
                    
                    // Push onto the stack for aftermost comparison
                    $this->stack->push($token);
                    
                    break;
                    
                case Token::T_CLOSE_TAG:
                case Token::T_CLOSE:
                    
                    // Match the current token with the previous T_OPEN_TAG
                    if ($this->matchTokens($this->stack->top(), $token))
                        $this->stack->pop();
                    
                    break;
                    
                case Token::T_VALUE:
                case Token::T_END:
                case Token::T_ATTRIBUTE:
                
                    break;
                    
                case Token::T_TEXT:
                    
                    break;
            }
        }
        return $this->stack;
    }
    
    protected function matchTokens(Token $t1, Token $t2)
    {
        // Comparing T_OPEN_TAG with T_CLOSE_TAG
        if ($t2->getType() == Token::T_CLOSE_TAG)
            if ( ($t1->getNamespace() == $t2->getNamespace()) && ($t1->getName() == $t2->getName()) )
                return true;
                
        // Comparing T_OPEN_TAG with T_CLOSE
        elseif ($t2->getType() == Token::T_CLOSE)
            return true;
        
        return false;
    }
}