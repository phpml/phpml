<?php

namespace phpml\lib\parser;

use phpml\lib\parser\token\Token;

/**
 * EndParser class
 *
 * @author Thiago Rigo <thiagophx@gmail.com>
 * @package lib
 * @subpackage parser
 */
class EndParser implements TokenParser
{
    public static function parse(Scanner $scanner)
    {
        // We eat the character >
        $scanner->forward(1);
        
        // Next lookAhead
        $scanner->setLookAhead(Token::T_OPEN_TAG|Token::T_CLOSE_TAG|Token::T_TEXT);
        
        // T_END token found
        return new Token(Token::T_END);
    }
}
