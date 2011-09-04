<?php

namespace phpml\lib\parser;

use phpml\lib\exception\util\ExceptionFactory,
    phpml\lib\parser\token\Token;

/**
 * Parser class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser
 */
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
    
    public function parse()
    {
        $token = null;
        
        while (($token = $this->scanner->nextToken()) != false) {
            
            switch ($token->getType()) {
                
                case Token::T_OPEN_TAG:
                    
                    // Push onto the stack for aftermost comparison
                    $this->stack->push($token);
                    
                    // Start building the component
                    $this->componentBuilder->setOpenTag($token);
                    
                    break;
                    
                case Token::T_CLOSE_TAG:
                case Token::T_CLOSE:
                    
                    // Match the current token with the previous T_OPEN_TAG
                    if ($this->matchTokens($this->stack->top(), $token))
                        $this->stack->pop();
                        
                    // Exception
                    else
                        throw ExceptionFactory::createUnexpectedToken(
                            __FILE__, 
                            __LINE__, 
                            $this->scanner->getFile()->getFileName(), 
                            $this->scanner->getFile()->getCurrentLine(),
                            $token
                        );
                    
                    // Build the component and add into the tree
                    $this->tree->push($this->componentBuilder->build());
                        
                    break;

                case Token::T_END:
                    
                    break;
                    
                case Token::T_ATTRIBUTE:
                    
                    // Add the T_ATTRIBUTE into the ComponentBuilder
                    $this->componentBuilder->addAttr($token);
                    
                    break;
                    
                case Token::T_VALUE:
                    
                    // Add the T_VALUE into the ComponentBuilder
                    $this->componentBuilder->addValue($token);
                    
                    break;
                    
                case Token::T_TEXT:
                    
                    // Add the T_TEXT into the Tree
                    $this->tree->push($token->getValue());
                    
                    break;
            }
        }
        return $this->tree;
    }
    
    protected function matchTokens(Token $t1, Token $t2)
    {
        // Comparing T_OPEN_TAG with T_CLOSE_TAG
        if ($t2->getType() == Token::T_CLOSE_TAG) {
            if ( ($t1->getNamespace() == $t2->getNamespace()) && ($t1->getName() == $t2->getName()) )
                return true;
                
        // Comparing T_OPEN_TAG with T_CLOSE
        } elseif ($t2->getType() == Token::T_CLOSE) {
            return true;
        }
        
        return false;
    }
}