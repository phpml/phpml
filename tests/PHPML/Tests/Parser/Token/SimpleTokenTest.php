<?php

namespace PHPML\Tests\Parser\Token;

use PHPML\Parser\Token\SimpleToken,
    PHPML\Parser\Token\Token;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * SimpleToken test case
 */
class SimpleTokenTest extends \PHPUnit_Framework_TestCase
{    
    public function testGetValueAndType()
    {
        $simpleToken = new SimpleToken(Token::T_TEXT, '<p>Thiago</p>');
        
        $this->assertSame(Token::T_TEXT, $simpleToken->getType());
        $this->assertSame('<p>Thiago</p>', $simpleToken->getValue());
    }

}
