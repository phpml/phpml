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
                $char = $this->nextChar();
                $this->file->goBack();
                
                switch ($char) {
                    case $this->isLetter($char):
                        return $this->parseAttribute();

                        break;

                    case '>':
                        parseEnd();

                        self::$lookAhead = Token::T_OPEN_TAG|Token::T_CLOSE_TAG|Token::T_TEXT;
                        return new token\SimpleToken($type, $value);
                        break;;

                    case '/':
                        parseClose();
                        break;

                    default:
                        // mostra os tokens esperados
                        erro();
                }
                break;

            case Token::T_VALUE:
                $char = $this->nextChar();
                $this->file->goBack();
                
                switch ($char) {
                    case ( ($char == '"') || ($char == "'") ):
                        return $this->parseValue();
                        break;

                    default:
                        // mostra os tokens esperados
                        erro();
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

    public function parseValue()
    {
        $char  = $this->file->nextChar();
        $state = 0;
        $value = '';
        $pos   = 0;

        while (true) {
            switch ($state) {
                case 0:
                    if ($char == '"') {
                       $state = 1;
                       $pos   = $this->file->find('"');
                    } else if ($char == "'") {
                       $state = 2;
                       $pos   = $this->file->find("'");
                    } else {
                        throw ExceptionFactory::createUnexpectedChar(
                                __FILE__,
                                __LINE__,
                                $this->file->getFileName(),
                                $this->file->getCurrentLine(),
                                $char
                        );
                    }

                    break;

                case 1:
                    if ($pos === false) {
                        throw ExceptionFactory::createCannotFindChar(
                                __FILE__,
                                __LINE__,
                                $this->file->getFileName(),
                                $this->file->getCurrentLine(),
                                '"'
                        );
                    }

                    $value .= $this->forward($pos);
                    $this->forward(1);

                    break 2;

                case 2:
                    if ($pos === false) {
                        throw ExceptionFactory::createCannotFindChar(
                                __FILE__,
                                __LINE__,
                                $this->file->getFileName(),
                                $this->file->getCurrentLine(),
                                "'"
                        );
                    }
                    
                    $value .= $this->forward($pos);
                    $this->forward(1);
                    
                    break 2;
            }
        }

        self::$lookAhead = Token::T_ATTRIBUTE|Token::T_END|Token::T_CLOSE;
        return new SimpleToken(Token::T_VALUE, $value);
    }

    public function parseAttribute()
    {
        $char  = $this->file->nextChar();
        $state = 0;
        $value = '';

        while (true) {
            switch ($state) {
                case 0:
                    if ( ($this->isLetter($char)) || ($char == '_') ) {
                        $state  = 1;
                        $value .= $char;
                        $char = $this->file->nextChar();
                    } else {
                        throw ExceptionFactory::createUnexpectedChar(
                                __FILE__,
                                __LINE__,
                                $this->file->getFileName(),
                                $this->file->getCurrentLine(),
                                $char
                        );
                    }
                    
                    break;

                case 1:
                    if ( ($this->isAlpha($char)) || ($char == '_') ) {
                        $state  = 1;
                        $value .= $char;
                        $char = $this->file->nextChar();
                    } else if ($this->isSpace($char)) {
                        $state = 2;
                        $char = $this->nextChar();
                    } else if ($char == '=') {
                        break 2;
                    } else {
                        throw ExceptionFactory::createUnexpectedChar(
                                __FILE__,
                                __LINE__,
                                $this->file->getFileName(),
                                $this->file->getCurrentLine(),
                                $char
                        );
                    }

                    break;

                case 2:
                    if ($char == '=') {
                        break 2;
                    } else {
                        throw ExceptionFactory::createUnexpectedChar(
                                __FILE__,
                                __LINE__,
                                $this->file->getFileName(),
                                $this->file->getCurrentLine(),
                                $char
                        );
                    }

                    break;
            }
        }

        self::$lookAhead = Token::T_VALUE;
        return new SimpleToken(Token::T_ATTRIBUTE, $value);
    }

    public function parseOpenTag()
    {
        $char  = $this->file->nextChar();
        $state = 0;
        $ns    = '';
        $name  = '';

        while (true) {
            switch ($state) {
                case 0:
                    if ($char == '<') {
                        $state = 1;
                        $char  = $this->file->nextChar();
                    } else {
                        throw ExceptionFactory::createUnexpectedChar(
                                __FILE__,
                                __LINE__,
                                $this->file->getFileName(),
                                $this->file->getCurrentLine(),
                                $char
                        );
                    }

                    break;

                case 1:
                    if ( ($this->isLetter($char)) || ($char == '_') ) {
                        $state = 2;
                        $ns   .= $char;
                        $char  = $this->file->nextChar();
                    } else {
                        throw ExceptionFactory::createUnexpectedChar(
                                __FILE__,
                                __LINE__,
                                $this->file->getFileName(),
                                $this->file->getCurrentLine(),
                                $char
                        );
                    }
                    
                    break;

                case 2:
                    if ( ($this->isAlpha($char)) || ($char == '_') ) {
                        $state = 2;
                        $ns   .= $char;
                        $char  = $this->file->nextChar();
                    } else if ($char == ':') {
                        $state = 3;
                        $char  = $this->file->nextChar();
                    } else {
                        throw ExceptionFactory::createUnexpectedChar(
                                __FILE__,
                                __LINE__,
                                $this->file->getFileName(),
                                $this->file->getCurrentLine(),
                                $char
                        );
                    }

                    break;

                case 3:
                    if ( ($this->isLetter($char)) || ($char == '_') ) {
                        $state = 4;
                        $name .= $char;
                        $char  = $this->file->nextChar();
                    } else {
                        throw ExceptionFactory::createUnexpectedChar(
                                __FILE__,
                                __LINE__,
                                $this->file->getFileName(),
                                $this->file->getCurrentLine(),
                                $char
                        );
                    }

                    break;

                case 4:
                    if ( ($this->isAlpha($char)) || ($char == '_') ) {
                        $state = 4;
                        $name .= $char;
                        $char  = $this->file->nextChar();
                    } else if ($this->isSpace($char)) {
                        break 2;
                    } else {
                        throw ExceptionFactory::createUnexpectedChar(
                                __FILE__,
                                __LINE__,
                                $this->file->getFileName(),
                                $this->file->getCurrentLine(),
                                $char
                        );
                    }

                    break;
            }
        }

        self::$lookAhead = Token::T_ATTRIBUTE|Token::T_END|Token::T_CLOSE;
        return new TagToken(Token::T_OPEN_TAG, $ns, $name);
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
