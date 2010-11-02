<?php

namespace phpml\parser;

use phpml\parser\token\Token, 
    phpml\parser\token\SimpleToken,
    phpml\parser\token\TagToken,
    phpml\exception\ParserException,
    phpml\exception\util\ExceptionType;


class Scanner
{
    protected $file;
    protected static $lookAhead;

    public function __construct(File $file)
    {
        $this->file = $file;
        self::$lookAhead = Token::T_TEXT|Token::T_OPEN_TAG;
    }

    // new generation
    // talvez fazer um metodo para cada state
    public function nextToken()
    {
        switch (self::$lookAhead) {
            case Token::T_TEXT|Token::T_OPEN_TAG:

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
                    $this->parseOpenTag();
                }

                break;

            case Token::T_ATTRIBUTE|Token::T_END|Token::T_CLOSE:
                $char = proximoCharNaoEspaco();
                switch ($char) {
                    case isLetter():
                        parseAttr();

                        self::$lookAhead = Token::T_VALUE;
                        return new token\SimpleToken($type, $value);
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
                $char = proximoCharNaoEspaco();
                switch ($char) {
                    case '"':
                        parseValueDoubleQuotes();
                        break;

                    case '\'':
                        parseValueSigleQuotes();
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

                $this->file->saveState();
                $char = $this->nextChar('<');
                $this->file->restoreState();

                if ($this->isLetter($char)) {
                    return $this->parseOpenTag();
                } else if ($char == '/') {
                    parseCloseTag();
                } else {
                    error();
                }

                break;

            default:
                // estado desconhecido
                 erro();
        }
    }

    public function parseOpenTag()
    {
        $char  = $this->file->getNextChar();
        $state = 0;
        $ns    = '';
        $name  = '';

        while (true) {
            switch ($state) {
                case 0:
                    if ($char == 'a') {
                        $state = 1;
                        $char  = $this->file->getNextChar();
                    } else {
                        throw new ParserException(ExceptionType::UNEXPECTED_CHAR,
                                $this->file->getFileName(),
                                $this->file->getCurrentLine(),
                                $char);
                    }

                    break;

                case 1:
                    echo 1;
                    if ( ($this->isLetter($char)) || ($char == '_') ) {
                        $state = 2;
                        $ns   .= $char;
                        $char  = $this->file->getNextChar();
                    } else {
                        // exception
                    }
                    
                    break;

                case 2:
                    echo 2;
                    if ( ($this->isLetter($char)) || ($char == '_') || (ctype_digit($char)) ) {
                        $state = 2;
                        $ns   .= $char;
                        $char  = $this->file->getNextChar();
                    } else if ($char == ':') {
                        $state = 3;
                        $char  = $this->file->getNextChar();
                    } else {
                        // exception
                    }

                    break;

                case 3:
                    echo 3;
                    if ( ($this->isLetter($char)) || ($char == '_') ) {
                        $state = 4;
                        $name .= $char;
                        $char  = $this->file->getNextChar();
                    } else {
                        // exception
                    }

                    break;

                case 4:
                    echo 4;
                    if ( ($this->isLetter($char)) || ($char == '_') || (ctype_digit($char)) ) {
                        $state = 4;
                        $name .= $char;
                        $char  = $this->file->getNextChar();
                    } else if ($this->isSpace($char)) {
                        break 2;
                    } else {
                        // exception
                    }

                    break;
            }
        }

        self::$lookAhead = Token::T_ATTRIBUTE|Token::T_END|Token::T_CLOSE;
        return new TagToken(Token::T_OPEN_TAG, $ns, $name);
    }

    public function nextChar($allowed = null)
    {
        $allowed = (string) $allowed;
        while (! $this->file->isEOF()) {

            $char = $this->file->getNextChar();

            if ($this->isSpace($char))
                continue;

            if ($char === $allowed)
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
            $text .= $this->file->getNextChar();
            $readPos++;
        }

        return $text;
    }

    /*    public function getNextToken()
    {
        if ($this->file->isEOF())
            return false;

        $firstChar = $this->seekToNextChar();
        $token     = null;

        $this->file->saveState();

        $token = $this->nextTag();
    }
    
 */

    protected function isLetter($char)
    {
        return (bool) preg_match('/[a-zA-Z]/', $char);
    }

    protected function isSpace($char)
    {
        return (bool) preg_match('/\s/', $char);
    }

}
