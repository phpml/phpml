<?php

namespace phpml\parser;

use phpml\parser\token\Token, 
    phpml\parser\token\SimpleToken,
    phpml\parser\token\TagToken,
    phpml\exception\ParserException,
    phpml\exception\util\ExceptionFactory;


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
                } else if ($pos > 0) {
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
                } else if ($char == '>') {
                    parseEnd();
                    self::$lookAhead = Token::T_OPEN_TAG|Token::T_CLOSE_TAG|Token::T_TEXT;
                    return new token\SimpleToken($type, $value);

                // T_CLOSE
                } else if ($char == '/') {
                    return $this->parseClose();

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
                $char = proximoCharNaoEspaco();
                switch ($char) {
                    // busca open tag pelos namespaces definidos
                    case openTag():
                        parseOpenTag();
                        break;

                    case closeTag():
                        parseCloseTag();
                        break;

                    case text():
                        parseText();
                        break;;

                    default:
                        // mostra os tokens esperados
                        erro();
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
                    parseCloseTag();
                }

                break;

            default:
                // estado desconhecido
                 erro();
        }
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
                    } else if ($char == "'") {
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
                    } else if ($this->isSpace($char)) {
                        $state = 2;
                        $char  = $this->nextChar();

                    // If the next char is =, we're done
                    } else if ($char == '=') {
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
                    } else if ($char == ':') {
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
