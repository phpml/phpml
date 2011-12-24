<?php

namespace PHPML\Tests\Parser\Token;

use PHPML\Parser\Token\Token,
    PHPML\Parser\Token\TagToken;

require_once __DIR__ . '/../../../../bootstrap.php';

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
