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
        $this->componentBuilder = new ComponentBuilder($scanner->getFile());
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
                    // T_CLOSE_TAG doesn't need to be pushed into the tree nor built
                    if ($token->getType() == Token::T_CLOSE) {

                        // It's not a child, so put it into the Tree
                        if (count($this->stack) == 0)
                            $this->tree->addNoChild($this->componentBuilder->build());
                        else
                            $this->tree->addNoChild($this->componentBuilder->build(), $this->tree->top());

                    // We removed the token from the stack, so we have to set the new top component
                    } else {
                        $this->tree->setTop($this->tree->top()->getParent());
                    }
                        
                    break;

                case Token::T_END:
                    
                    // Build the component and add into the tree
                    if (count($this->stack) == 1)
                        $this->tree->add($this->componentBuilder->build());
                    else
                        $this->tree->add($this->componentBuilder->build(), $this->tree->top());
                    
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
                    
                    // Add the value of T_TEXT into the Tree
                    if ($this->stack->isEmpty())
                        $this->tree->addText($token);
                        
                    // Add the value of T_TEXT into its parent component
                    else
                        $this->tree->addText($token, $this->tree->top());
                    
                    break;
            }
        }
        
        // Check if there is no tokens left into the stack
        $this->verifyStack();
        
        return $this->tree;
    }
    
    protected function verifyStack()
    {
        // If the stack is not empty, we have a problem
        if (!$this->stack->isEmpty()) {
            
            // Get the first remaining token into the stack and throw an exception
            throw ExceptionFactory::createTagNotClosed(
                __FILE__, 
                __LINE__, 
                $this->scanner->getFile()->getFileName(), 
                $this->stack->top()
            );
        }
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