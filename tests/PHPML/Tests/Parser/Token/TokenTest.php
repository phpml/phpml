<?php

namespace PHPML\Tests\Parser\Token;

use PHPML\Parser\Token\Token;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * Token test case
 */
class TokenTest extends \PHPUnit_Framework_TestCase
{       
    public function testGetType()
    {
        $token = new Token(Token::T_OPEN_TAG);
        
        $this->assertSame(Token::T_OPEN_TAG, $token->getType());
    }

}
