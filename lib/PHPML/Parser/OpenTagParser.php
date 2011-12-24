<?php

namespace PHPML\Parser;

use PHPML\Exception\Util\ExceptionFactory,
    PHPML\Parser\Token\Token,
    PHPML\Parser\Token\TagToken;

/**
 * OpenTagParser class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser
 */
class OpenTagParser implements TokenParser
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

                    // The first char after < can be [a-zA-Z] or _
                    if ( ($scanner->isLetter($char)) || ($char == '_') ) {
                        $state = 2;
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

                case 2:

                    // From the second char after < onwards can be [a-zA-Z0-9] or _
                    if ( ($scanner->isAlpha($char)) || ($char == '_') ) {
                        $state = 2;
                        $ns   .= $char;
                        $char  = $file->nextChar();

                    // If the next char is :, we already have the namespace
                    } elseif ($char == ':') {
                        $state = 3;
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

                    // The first char after : can be [a-zA-Z] or _
                    if ( ($scanner->isLetter($char)) || ($char == '_') ) {
                        $state = 4;
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

                case 4:

                    // From the second char after : onwards can be [a-zA-Z0-9] or _
                    if ( ($scanner->isAlpha($char)) || ($char == '_') ) {
                        $state = 4;
                        $name .= $char;
                        $char  = $file->nextChar();

                    // If the next char is \s, we got the name
                    } elseif ($scanner->isSpace($char)) {
                        break 2;

                    // If the next char is >, we got the T_END
                    } elseif ($char == '>') {
                        $file->goBack();
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
        $scanner->setLookAhead(Token::T_ATTRIBUTE|Token::T_END|Token::T_CLOSE);

        // T_REGISTER
        if ( ($ns == 'php') && ($name == 'Register') )
            return new TagToken(Token::T_REGISTER, $ns, $name);
        
        // T_OPEN_TAG token found
        return new TagToken(Token::T_OPEN_TAG, $ns, $name);
    }
}
