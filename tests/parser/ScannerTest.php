<?php

namespace phpml\tests\parser;

use phpml\lib\parser\token\Token;

use phpml\lib\parser\Scanner,
    phpml\lib\parser\File;
    
require_once __DIR__ . '/../bootstrap.php';

/**
 * Test class for Scanner.
 */
class ScannerTest extends \PHPUnit_Framework_TestCase
{
    public function testLetter()
    {
        $scanner = new Scanner(new File(FILES_DIR . 'empty_file'));
        
        $this->assertFalse($scanner->isLetter(''));
        $this->assertFalse($scanner->isLetter('-'));
        $this->assertFalse($scanner->isLetter(9));
        $this->assertFalse($scanner->isLetter('9'));
        
        $this->assertTrue($scanner->isLetter('a'));
        $this->assertTrue($scanner->isLetter('A'));
    }
    
    public function testAlpha()
    {
        $scanner = new Scanner(new File(FILES_DIR . 'empty_file'));
        
        $this->assertFalse($scanner->isAlpha(''));
        $this->assertFalse($scanner->isAlpha('-'));
        
        $this->assertTrue($scanner->isAlpha(9));
        $this->assertTrue($scanner->isAlpha('9'));
        $this->assertTrue($scanner->isAlpha('a'));
        $this->assertTrue($scanner->isAlpha('A'));
    }
    
    public function testSpace()
    {
        $scanner = new Scanner(new File(FILES_DIR . 'empty_file'));
        
        $this->assertFalse($scanner->isSpace(''));
        $this->assertFalse($scanner->isSpace('-'));
        $this->assertFalse($scanner->isSpace('a'));
        $this->assertFalse($scanner->isSpace('9'));
        $this->assertFalse($scanner->isSpace(5));
        
        $this->assertTrue($scanner->isSpace(' '));
        $this->assertTrue($scanner->isSpace("\n"));
        $this->assertTrue($scanner->isSpace("\t"));
    }
    
    public function testReadingAll()
    {
        $scanner = new Scanner(new File(FILES_DIR . 'not_empty_file'));
        
        $this->assertEquals('not_empty_file', $scanner->forward());
        $this->assertTrue($scanner->getFile()->isEOF());        
    }
    
    public function testLookAhead()
    {
        $scanner = new Scanner(new File(FILES_DIR . 'not_empty_file'));
        $property = new \ReflectionProperty('phpml\\lib\\parser\\Scanner', 'lookAhead');
        $property->setAccessible(true);
        
        $this->assertEquals(Token::T_OPEN_TAG|Token::T_TEXT, $property->getValue($scanner));
        
        $scanner->setLookAhead(Token::T_ATTRIBUTE);
        $this->assertEquals(Token::T_ATTRIBUTE, $property->getValue($scanner));
    }
    
    public function testForwarding()
    {
        $scanner = new Scanner(new File(FILES_DIR . 'not_empty_file'));
        
        $scanner->getFile()->nextChar();
        $this->assertEquals('ot', $scanner->forward(2));
        
        $scanner->getFile()->nextChar();
        $this->assertEquals('empty_file', $scanner->forward(20));
    }
    
    public function testNextCharNewLine()
    {
        $scanner = new Scanner(new File(FILES_DIR . 'two_lines_file'));
        
        $this->assertEquals('2', $scanner->nextChar());
    }
    
    public function testNextCharSpaces()
    {
        $scanner = new Scanner(new File(FILES_DIR . 'spaces_file'));
        
        $this->assertEquals('t', $scanner->nextChar());
        $this->assertEquals('h', $scanner->nextChar());
        $this->assertFalse($scanner->getFile()->isEOF());
        $this->assertFalse($scanner->nextChar());
        $this->assertTrue($scanner->getFile()->isEOF());
        $this->assertFalse($scanner->nextChar());
        $this->assertTrue($scanner->getFile()->isEOF());
    }
}
