<?php

namespace phpml\lib\parser;

use phpml\lib\exception\util\ExceptionFactory,
    phpml\lib\parser\token\Token,
    phpml\lib\parser\token\SimpleToken;

/**
 * ValueParser class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser
 */
class ValueParser implements TokenParser
{
    public static function parse(Scanner $scanner)
    {
        $file  = $scanner->getFile();
        $char  = $file->nextChar();
        $state = 0;
        $value = '';
        $pos   = 0;

        while (true) {
            switch ($state) {
                case 0:

                    // Ex: "value"
                    if ($char == '"') {
                       $state = 1;
                       $pos   = $file->find('"');

                    // Ex: 'value'
                    } elseif ($char == "'") {
                       $state = 2;
                       $pos   = $file->find("'");

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

                    // Char " not found
                    if ($pos === false) {
                        throw ExceptionFactory::createCannotFindChar(
                                __FILE__,
                                __LINE__,
                                $file->getFileName(),
                                $file->getCurrentLine(),
                                '"'
                        );
                    }

                    // Get the value
                    $value .= $scanner->forward($pos);

                    // Bypass "
                    $scanner->forward(1);

                    break 2;

                case 2:

                    // Char ' not found
                    if ($pos === false) {
                        throw ExceptionFactory::createCannotFindChar(
                                __FILE__,
                                __LINE__,
                                $file->getFileName(),
                                $file->getCurrentLine(),
                                "'"
                        );
                    }

                    // Get the value
                    $value .= $scanner->forward($pos);

                    // Bypass '
                    $scanner->forward(1);
                    
                    break 2;
            }
        }

        // Next lookAhead
        $scanner->setLookAhead(Token::T_ATTRIBUTE|Token::T_END|Token::T_CLOSE);

        // T_VALUE token found
        return new SimpleToken(Token::T_VALUE, $value);
    }
}