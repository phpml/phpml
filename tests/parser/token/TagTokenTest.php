<?php

namespace phpml\tests\parser\token;

use phpml\lib\parser\token\TagToken,
    phpml\lib\parser\token\Token;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * TagToken test case
 */
class TagTokenTest extends \PHPUnit_Framework_TestCase
{
    public function testGetNamespaceNameAndType()
    {
        $tagToken = new TagToken(Token::T_OPEN_TAG, 'phpml', 'Label');
        
        $this->assertSame(Token::T_OPEN_TAG, $tagToken->getType());
        $this->assertSame('phpml', $tagToken->getNamespace());
        $this->assertSame('Label', $tagToken->getName());
    }
    
}
