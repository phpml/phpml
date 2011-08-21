<?php

namespace phpml\lib\parser;

use phpml\lib\exception\util\ExceptionFactory,
    phpml\lib\parser\token\Token,
    phpml\lib\parser\token\SimpleToken;

/**
 * AttributeParser class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser
 */
class AttributeParser implements TokenParser
{
    public static function parse(Scanner $scanner)
    {
        $file  = $scanner->getFile();
        $char  = $file->nextChar();
        $state = 0;
        $value = '';

        while (true) {
            switch ($state) {
                case 0:

                    // T_ATTRIBUTE begins with [a-zA-Z] or _
                    if ( ($scanner->isLetter($char)) || ($char == '_') ) {
                        $state  = 1;
                        $value .= $char;
                        $char   = $file->nextChar();

                    // Exception
                    } else {

                        // Unexpected EOF
                        if ($char == false) {
                            throw ExceptionFactory::createUnexpectedEOF(
                                __FILE__,
                                __LINE__,
                                $file->getFileName(),
                                $file->getCurrentLine()
                            );

                        // Unexpected Char
                        } else {
                            throw ExceptionFactory::createUnexpectedChar(
                                __FILE__,
                                __LINE__,
                                $file->getFileName(),
                                $file->getCurrentLine(),
                                $char
                            );
                        }
                    }
                    
                    break;

                case 1:

                    // The second char can be [a-zA-Z0-9] or _
                    if ( ($scanner->isAlpha($char)) || ($char == '_') ) {
                        $state  = 1;
                        $value .= $char;
                        $char   = $file->nextChar();

                    // Space indicates the end of the T_ATTRIBUTE
                    // But we have to eat the next = char
                    } elseif ($scanner->isSpace($char)) {
                        $state = 2;
                        $char  = $scanner->nextChar();

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
                                $file->getFileName(),
                                $file->getCurrentLine()
                            );

                        // Unexpected Char
                        } else {
                            throw ExceptionFactory::createUnexpectedChar(
                                __FILE__,
                                __LINE__,
                                $file->getFileName(),
                                $file->getCurrentLine(),
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
                                $file->getFileName(),
                                $file->getCurrentLine()
                            );

                        // Unexpected Char
                        } else {
                            throw ExceptionFactory::createUnexpectedChar(
                                __FILE__,
                                __LINE__,
                                $file->getFileName(),
                                $file->getCurrentLine(),
                                $char
                            );
                        }
                    }

                    break;
            }
        }

        // Next lookAhead
        $scanner->setLookAhead(Token::T_VALUE);

        // T_ATTRIBUTE token found
        return new SimpleToken(Token::T_ATTRIBUTE, $value);
    }
}
