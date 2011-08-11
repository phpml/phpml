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
    protected static $lookAhead;

    public function __construct(File $file)
    {
        $this->file = $file;
        self::$lookAhead = Token::T_TEXT|Token::T_OPEN_TAG;
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
                    self::$lookAhead = Token::T_OPEN_TAG|Token::T_CLOSE_TAG;
                    return new SimpleToken(Token::T_TEXT, $this->forward($pos));
                    
                // T_OPEN_TAG
                } else {
                    return $this->parseOpenTag();
                }

                break;

            case Token::T_ATTRIBUTE|Token::T_END|Token::T_CLOSE:

                // Get the next char to verify the next token
                $char = $this->nextChar();
                $this->file->goBack();
                
                // T_ATTRIBUTE
                if ($this->isLetter($char)) {
                    return $this->parseAttribute();

                // T_END
                } elseif ($char == '>') {
                    $this->forward(1);
                    self::$lookAhead = Token::T_OPEN_TAG|Token::T_CLOSE_TAG|Token::T_TEXT;
                    return new Token(Token::T_END);

                // T_CLOSE
                } elseif ($char == '/') {
                    return $this->parseClose();

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
                    return $this->parseValue();

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
                            self::$lookAhead = Token::T_OPEN_TAG|Token::T_CLOSE_TAG;
                            return new SimpleToken(Token::T_TEXT, $this->forward($posOpenTag));
                        }

                    // T_CLOSE_TAG comes first
                    } else {
                        
                        // We have T_TEXT
                        if ($posCloseTag > 0) {
                            self::$lookAhead = Token::T_OPEN_TAG|Token::T_CLOSE_TAG;
                            return new SimpleToken(Token::T_TEXT, $this->forward($posCloseTag));
                        }
                    }

                // T_OPEN_TAG found
                } elseif ($posOpenTag !== false) {
                    
                    // We have T_TEXT
                    if ($posOpenTag > 0) {
                        self::$lookAhead = Token::T_OPEN_TAG|Token::T_CLOSE_TAG;
                        return new SimpleToken(Token::T_TEXT, $this->forward($posOpenTag));
                    }

                    // We have T_OPEN_TAG
                    return $this->parseOpenTag();

                // T_CLOSE_TAG found
                } else {
                    
                    // We have T_TEXT
                    if ($posCloseTag > 0) {
                        self::$lookAhead = Token::T_OPEN_TAG|Token::T_CLOSE_TAG;
                        return new SimpleToken(Token::T_TEXT, $this->forward($posCloseTag));
                    }

                    // We have T_CLOSE_TAG
                    return $this->parseCloseTag();
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

                if ($this->isLetter($char)) {
                    return $this->parseOpenTag();
                } else {
                    return $this->parseCloseTag();
                }

                break;

            default:
                // estado desconhecido
                 erro();
        }
    }

    protected function parseCloseTag()
    {
        $char  = $this->file->nextChar();
        $state = 0;
        $ns    = '';
        $name  = '';

        while (true) {
            switch ($state) {
                case 0:

                    // Must start with <
                    if ($char == '<') {
                        $state = 1;
                        $char  = $this->file->nextChar();

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

                case 1:

                    // Second char must be /
                    if ($char == '/') {
                        $state = 2;
                        $char  = $this->file->nextChar();

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

                case 2:

                    // The first char after / can be [a-zA-Z] or _
                    if ( ($this->isLetter($char)) || ($char == '_') ) {
                        $state = 3;
                        $ns   .= $char;
                        $char  = $this->file->nextChar();

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

                case 3:

                    // From the second char after / onwards can be [a-zA-Z0-9] or _
                    if ( ($this->isAlpha($char)) || ($char == '_') ) {
                        $state = 3;
                        $ns   .= $char;
                        $char  = $this->file->nextChar();

                    // If the next char is :, we already have the namespace
                    } elseif ($char == ':') {
                        $state = 4;
                        $char  = $this->file->nextChar();

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

                case 4:

                    // The first char after : can be [a-zA-Z] or _
                    if ( ($this->isLetter($char)) || ($char == '_') ) {
                        $state = 5;
                        $name .= $char;
                        $char  = $this->file->nextChar();

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

                        // Illegal space after :
                        } elseif ($this->isSpace($char)) {
                            throw ExceptionFactory::createIllegalSpace(
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

                case 5:

                    // From the second char after : onwards can be [a-zA-Z0-9] or _
                    if ( ($this->isAlpha($char)) || ($char == '_') ) {
                        $state = 5;
                        $name .= $char;
                        $char  = $this->file->nextChar();

                    // If the next char is >, we got the name
                    } elseif ($char == '>') {
                        break 2;

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

                        // Illegal space before >
                        } elseif ($this->isSpace($char)) {
                            throw ExceptionFactory::createIllegalSpace(
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
            }
        }

        // Next lookAhead
        self::$lookAhead = Token::T_OPEN_TAG|Token::T_CLOSE_TAG|Token::T_TEXT;

        // T_CLOSE_TAG found token
        return new TagToken(Token::T_CLOSE_TAG, $ns, $name);
    }

    protected function parseClose()
    {
        // Must be /
        $char = $this->file->nextChar();

        // Unexpected char
        if ($char != '/') {

            // Unexpected EOF
            if ($char === false) {
                throw ExceptionFactory::createUnexpectedEOF(
                    __FILE__,
                    __LINE__,
                    $this->file->getFileName(),
                    $this->file->getCurrentLine()
                );

            // Unexpected char
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

        // Must be >
        $char = $this->file->nextChar();

        // We have a problem here
        if ($char != '>') {

            // Illegal space
            if ($this->isSpace($char)) {
                throw ExceptionFactory::createIllegalSpace(
                    __FILE__,
                    __LINE__,
                    $this->file->getFileName(),
                    $this->file->getCurrentLine()
                );

            // Unexpected EOF
            } elseif ($char === false) {
                throw ExceptionFactory::createUnexpectedEOF(
                    __FILE__,
                    __LINE__,
                    $this->file->getFileName(),
                    $this->file->getCurrentLine()
                );

            // Unexpected char
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

        // Next lookAhead
        self::$lookAhead = Token::T_OPEN_TAG|Token::T_CLOSE_TAG|Token::T_TEXT;

        // T_CLOSE found token
        return new Token(Token::T_CLOSE);
    }

    // TODO: Is this right? Using find()
    // TODO: Add backslash for comments
    // Can have any char inside ' or "
    protected function parseValue()
    {
        $char  = $this->file->nextChar();
        $state = 0;
        $value = '';
        $pos   = 0;

        while (true) {
            switch ($state) {
                case 0:

                    // Ex: "value"
                    if ($char == '"') {
                       $state = 1;
                       $pos   = $this->file->find('"');

                    // Ex: 'value'
                    } elseif ($char == "'") {
                       $state = 2;
                       $pos   = $this->file->find("'");

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

                case 1:

                    // Char " not found
                    if ($pos === false) {
                        throw ExceptionFactory::createCannotFindChar(
                                __FILE__,
                                __LINE__,
                                $this->file->getFileName(),
                                $this->file->getCurrentLine(),
                                '"'
                        );
                    }

                    // Get the value
                    $value .= $this->forward($pos);

                    // Bypass "
                    $this->forward(1);

                    break 2;

                case 2:

                    // Char ' not found
                    if ($pos === false) {
                        throw ExceptionFactory::createCannotFindChar(
                                __FILE__,
                                __LINE__,
                                $this->file->getFileName(),
                                $this->file->getCurrentLine(),
                                "'"
                        );
                    }

                    // Get the value
                    $value .= $this->forward($pos);

                    // Bypass '
                    $this->forward(1);
                    
                    break 2;
            }
        }

        // Next lookAhead
        self::$lookAhead = Token::T_ATTRIBUTE|Token::T_END|Token::T_CLOSE;

        // T_VALUE token found
        return new SimpleToken(Token::T_VALUE, $value);
    }

    protected function parseAttribute()
    {
        $char  = $this->file->nextChar();
        $state = 0;
        $value = '';

        while (true) {
            switch ($state) {
                case 0:

                    // T_ATTRIBUTE begins with [a-zA-Z] or _
                    if ( ($this->isLetter($char)) || ($char == '_') ) {
                        $state  = 1;
                        $value .= $char;
                        $char   = $this->file->nextChar();

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

                case 1:

                    // The second char can be [a-zA-Z0-9] or _
                    if ( ($this->isAlpha($char)) || ($char == '_') ) {
                        $state  = 1;
                        $value .= $char;
                        $char   = $this->file->nextChar();

                    // Space indicates the end of the T_ATTRIBUTE
                    // But we have to eat the next = char
                    } elseif ($this->isSpace($char)) {
                        $state = 2;
                        $char  = $this->nextChar();

                    // If the next char is =, we're done
                    } elseif ($char == '=') {
                        break 2;

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

                case 2:

                    // If the next char is =, we're done
                    if ($char == '=') {
                        break 2;

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
            }
        }

        // Next lookAhead
        self::$lookAhead = Token::T_VALUE;

        // T_ATTRIBUTE found token
        return new SimpleToken(Token::T_ATTRIBUTE, $value);
    }

    // TODO: Exception when has space between : or <
    // Ex: <php :, <php: L, < php
    protected function parseOpenTag()
    {
        $char  = $this->file->nextChar();
        $state = 0;
        $ns    = '';
        $name  = '';

        while (true) {
            switch ($state) {
                case 0:

                    // Must start with <
                    if ($char == '<') {
                        $state = 1;
                        $char  = $this->file->nextChar();

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

                case 1:

                    // The first char after < can be [a-zA-Z] or _
                    if ( ($this->isLetter($char)) || ($char == '_') ) {
                        $state = 2;
                        $ns   .= $char;
                        $char  = $this->file->nextChar();

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

                case 2:

                    // From the second char after < onwards can be [a-zA-Z0-9] or _
                    if ( ($this->isAlpha($char)) || ($char == '_') ) {
                        $state = 2;
                        $ns   .= $char;
                        $char  = $this->file->nextChar();

                    // If the next char is :, we already have the namespace
                    } elseif ($char == ':') {
                        $state = 3;
                        $char  = $this->file->nextChar();
                    
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

                case 3:

                    // The first char after : can be [a-zA-Z] or _
                    if ( ($this->isLetter($char)) || ($char == '_') ) {
                        $state = 4;
                        $name .= $char;
                        $char  = $this->file->nextChar();

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

                        // Illegal space after :
                        } elseif ($this->isSpace($char)) {
                            throw ExceptionFactory::createIllegalSpace(
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

                case 4:

                    // From the second char after : onwards can be [a-zA-Z0-9] or _
                    if ( ($this->isAlpha($char)) || ($char == '_') ) {
                        $state = 4;
                        $name .= $char;
                        $char  = $this->file->nextChar();

                    // If the next char is \s, we got the name
                    } elseif ($this->isSpace($char)) {
                        break 2;

                    // If the next char is >, we got the T_END
                    } elseif ($char == '>') {
                        $this->file->goBack();
                        break 2;
                        
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
            }
        }

        // Next lookAhead
        self::$lookAhead = Token::T_ATTRIBUTE|Token::T_END|Token::T_CLOSE;

        // T_OPEN_TAG found token
        return new TagToken(Token::T_OPEN_TAG, $ns, $name);
    }

    protected function nextChar()
    {
        while (! $this->file->isEOF()) {

            $char = $this->file->nextChar();

            if ($this->isSpace($char))
                continue;

            return $char;
        }
        
        return false;
    }

    protected function forward($pos = null)
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

    protected function isLetter($char)
    {
        return (bool) preg_match('/[a-zA-Z]/', $char);
    }

    protected function isAlpha($char)
    {
        return (bool) preg_match('/[a-zA-Z0-9]/', $char);
    }

    protected function isSpace($char)
    {
        return (bool) preg_match('/\s/', $char);
    }

}
