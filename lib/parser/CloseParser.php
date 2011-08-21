<?php

namespace phpml\lib\parser;

use phpml\lib\exception\util\ExceptionFactory,
    phpml\lib\parser\token\Token;

/**
 * CloseParser class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser
 */
class CloseParser implements TokenParser
{
    public static function parse(Scanner $scanner)
    {
        $file = $scanner->getFile();
        
        // Must be /
        $char = $file->nextChar();

        // Unexpected char
        if ($char != '/') {

            // Unexpected EOF
            if ($char === false) {
                throw ExceptionFactory::createUnexpectedEOF(
                    __FILE__,
                    __LINE__,
                    $file->getFileName(),
                    $file->getCurrentLine()
                );

            // Unexpected char
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

        // Must be >
        $char = $file->nextChar();

        // We have a problem here
        if ($char != '>') {

            // Illegal space
            if ($scanner->isSpace($char)) {
                throw ExceptionFactory::createIllegalSpace(
                    __FILE__,
                    __LINE__,
                    $file->getFileName(),
                    $file->getCurrentLine()
                );

            // Unexpected EOF
            } elseif ($char === false) {
                throw ExceptionFactory::createUnexpectedEOF(
                    __FILE__,
                    __LINE__,
                    $file->getFileName(),
                    $file->getCurrentLine()
                );

            // Unexpected char
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

        // Next lookAhead
        $scanner->setLookAhead(Token::T_OPEN_TAG|Token::T_CLOSE_TAG|Token::T_TEXT);

        // T_CLOSE token found
        return new Token(Token::T_CLOSE);
    }
}
