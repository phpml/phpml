<?php

namespace phpml\lib\parser;

use phpml\lib\exception\util\ExceptionFactory,
    phpml\lib\parser\token\Token,
    phpml\lib\parser\token\TagToken;

/**
 * CloseTagParser class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser
 */
class CloseTagParser implements TokenParser
{
    public static function parse(Scanner $scanner)
    {
        $file  = $scanner->getFile();
        $char  = $file->nextChar();
        $state = 0;
        $ns    = '';
        $name  = '';

        while (true) {
            switch ($state) {
                case 0:

                    // Must start with <
                    if ($char == '<') {
                        $state = 1;
                        $char  = $file->nextChar();

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

                    // Second char must be /
                    if ($char == '/') {
                        $state = 2;
                        $char  = $file->nextChar();

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

                    // The first char after / can be [a-zA-Z] or _
                    if ( ($scanner->isLetter($char)) || ($char == '_') ) {
                        $state = 3;
                        $ns   .= $char;
                        $char  = $file->nextChar();

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

                case 3:

                    // From the second char after / onwards can be [a-zA-Z0-9] or _
                    if ( ($scanner->isAlpha($char)) || ($char == '_') ) {
                        $state = 3;
                        $ns   .= $char;
                        $char  = $file->nextChar();

                    // If the next char is :, we already have the namespace
                    } elseif ($char == ':') {
                        $state = 4;
                        $char  = $file->nextChar();

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

                case 4:

                    // The first char after : can be [a-zA-Z] or _
                    if ( ($scanner->isLetter($char)) || ($char == '_') ) {
                        $state = 5;
                        $name .= $char;
                        $char  = $file->nextChar();

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

                        // Illegal space after :
                        } elseif ($scanner->isSpace($char)) {
                            throw ExceptionFactory::createIllegalSpace(
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

                case 5:

                    // From the second char after : onwards can be [a-zA-Z0-9] or _
                    if ( ($scanner->isAlpha($char)) || ($char == '_') ) {
                        $state = 5;
                        $name .= $char;
                        $char  = $file->nextChar();

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
                                $file->getFileName(),
                                $file->getCurrentLine()
                            );

                        // Illegal space before >
                        } elseif ($scanner->isSpace($char)) {
                            throw ExceptionFactory::createIllegalSpace(
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
        $scanner->setLookAhead(Token::T_OPEN_TAG|Token::T_CLOSE_TAG|Token::T_TEXT);

        // T_CLOSE_TAG token found
        return new TagToken(Token::T_CLOSE_TAG, $ns, $name);
    }
}
