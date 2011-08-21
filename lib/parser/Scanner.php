<?php

namespace phpml\lib\parser;

use phpml\lib\parser\token\Token, 
    phpml\lib\parser\token\SimpleToken,
    phpml\lib\parser\token\TagToken,
    phpml\lib\exception\util\ExceptionFactory;

/**
 * Scanner class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser
 */
class Scanner
{
    protected $file;
    protected $lookAhead;

    public function __construct(File $file)
    {
        $this->file = $file;
        $this->lookAhead = Token::T_TEXT|Token::T_OPEN_TAG;
    }

    public function nextToken()
    {
        if ($this->file->isEOF())
            return false;
        
        switch (self::$lookAhead) {
            case Token::T_TEXT|Token::T_OPEN_TAG:

                // TODO: Find foreach registered namespace
                $pos = $this->file->find('<php:');
                
                // Nothing found
                if ($pos === false) {
                    return new SimpleToken(Token::T_TEXT, $this->forward());

                // Has T_TEXT to get
                } elseif ($pos > 0) {
                    $this->lookAhead = Token::T_OPEN_TAG|Token::T_CLOSE_TAG;
                    return new SimpleToken(Token::T_TEXT, $this->forward($pos));
                    
                // T_OPEN_TAG
                } else {
                    return OpenTagParser::parse($this);
                }

                break;

            case Token::T_ATTRIBUTE|Token::T_END|Token::T_CLOSE:

                // Get the next char to verify the next token
                $char = $this->nextChar();
                $this->file->goBack();
                
                // T_ATTRIBUTE
                if ($this->isLetter($char)) {
                    return AttributeParser::parse($this);

                // T_END
                } elseif ($char == '>') {
                    return EndParser::parse($this);

                // T_CLOSE
                } elseif ($char == '/') {
                    return CloseParser::parse($this);

                // Exception
                } else {

                    // Unexpected EOF
                    if ($char === false) {
                        throw ExceptionFactory::createUnexpectedEOF(
                                __FILE__,
                                __LINE__,
                                $this->file->getFileName(),
                                $this->file->getCurrentLine()
                        );

                    // Unexpected Char
                    } else {
                        throw ExceptionFactory::createUnexpectedChar(
                                __FILE__,
                                __LINE__,
                                $this->file->getFileName(),
                                $this->file->getCurrentLine(),
                                $char
                        );
                    }                    
                }

                break;

            case Token::T_VALUE:

                // Get the next char to verify the next token
                $char = $this->nextChar();
                $this->file->goBack();
                
                // T_VALUE
                if ( ($char == '"') || ($char == "'") ) {
                    return ValueParser::parse($this);

                // Exception
                } else {
                    
                    // Unexpected EOF
                    if ($char == false) {
                        throw ExceptionFactory::createUnexpectedEOF(
                                __FILE__,
                                __LINE__,
                                $this->file->getFileName(),
                                $this->file->getCurrentLine()
                        );

                    // Unexpected Char
                    } else {
                        throw ExceptionFactory::createUnexpectedChar(
                                __FILE__,
                                __LINE__,
                                $this->file->getFileName(),
                                $this->file->getCurrentLine(),
                                $char
                        );
                    }
                }

                break;

            case Token::T_OPEN_TAG|Token::T_CLOSE_TAG|Token::T_TEXT:

                // TODO: Find foreach registered namespace
                // Try to find T_OPEN_TAG
                $posOpenTag = $this->file->find('<php:');

                // Try to find T_CLOSE_TAG
                $posCloseTag = $this->file->find('</php:');

                // Nothing found
                if ( ($posOpenTag === false) && ($posCloseTag === false) ) {
                    
                    // There are some characters to parse
                    if ($this->nextChar() !== false) {
                        $this->file->goBack();
                        return new SimpleToken(Token::T_TEXT, $this->forward());
                    }
                    
                    // Nothing to parse, we're done
                    return;
                    
                // Both found
                } elseif ( ($posOpenTag !== false) && ($posCloseTag !== false) ) {

                    // T_OPEN_TAG comes first
                    if ($posOpenTag < $posCloseTag) {
                        
                        // We have T_TEXT
                        if ($posOpenTag > 0) {
                            $this->lookAhead = Token::T_OPEN_TAG|Token::T_CLOSE_TAG;
                            return new SimpleToken(Token::T_TEXT, $this->forward($posOpenTag));
                        }

                    // T_CLOSE_TAG comes first
                    } else {
                        
                        // We have T_TEXT
                        if ($posCloseTag > 0) {
                            $this->lookAhead = Token::T_OPEN_TAG|Token::T_CLOSE_TAG;
                            return new SimpleToken(Token::T_TEXT, $this->forward($posCloseTag));
                        }
                    }

                // T_OPEN_TAG found
                } elseif ($posOpenTag !== false) {
                    
                    // We have T_TEXT
                    if ($posOpenTag > 0) {
                        $this->lookAhead = Token::T_OPEN_TAG|Token::T_CLOSE_TAG;
                        return new SimpleToken(Token::T_TEXT, $this->forward($posOpenTag));
                    }

                    // We have T_OPEN_TAG
                    return OpenTagParser::parse($this);

                // T_CLOSE_TAG found
                } else {
                    
                    // We have T_TEXT
                    if ($posCloseTag > 0) {
                        $this->lookAhead = Token::T_OPEN_TAG|Token::T_CLOSE_TAG;
                        return new SimpleToken(Token::T_TEXT, $this->forward($posCloseTag));
                    }

                    // We have T_CLOSE_TAG
                    return CloseTagParser::parse($this);
                }

                break;

            case Token::T_OPEN_TAG|Token::T_CLOSE_TAG:

                // TODO: Find foreach registered namespace
                $pos = $this->file->find(array('</php:', '<php:'));

                // Nothing found
                if ($pos === false)
                    return false;

                $this->file->saveState();

                $this->forward(1);
                $char = $this->file->nextChar();

                $this->file->restoreState();

                if ($this->isLetter($char))
                    return OpenTagParser::parse($this);
                else
                    return CloseTagParser::parse($this);

                break;
        }
    }
    
    public function setLookAhead($lookAhead)
    {
        $this->lookAhead = $lookAhead;
    }

    public function nextChar()
    {
        while (! $this->file->isEOF()) {

            $char = $this->file->nextChar();

            if ($this->isSpace($char))
                continue;

            return $char;
        }
        
        return false;
    }

    public function forward($pos = null)
    {
        if (is_null($pos))
            return $this->file->readAll();

        $text    = '';
        $readPos = 0;

        while ( (! $this->file->isEOF()) && ($readPos < $pos) ) {
            $text .= $this->file->nextChar();
            $readPos++;
        }

        return $text;
    }

    public function isLetter($char)
    {
        return (bool) preg_match('/[a-zA-Z]/', $char);
    }

    public function isAlpha($char)
    {
        return (bool) preg_match('/[a-zA-Z0-9]/', $char);
    }

    public function isSpace($char)
    {
        return (bool) preg_match('/\s/', $char);
    }

}
