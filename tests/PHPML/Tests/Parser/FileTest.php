<?php

namespace PHPML\Tests\Parser;

use PHPML\Parser\File,
    PHPML\Parser\Token\Token;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * File test case
 */
class FileTest extends \PHPUnit_Framework_TestCase
{
    public function testInitialFileContext()
    {
        $file = new File(FILES_DIR . 'not_empty_file');
        
        $this->assertTrue(is_resource($file->getFilePointer()));
        $this->assertEquals($file->getCurrentLine(), 1);
        $this->assertEquals($file->getCurrentPos(), 0);
    }
    
    public function testEmptyFile()
    {
        $file = new File(FILES_DIR . 'empty_file');
        
        $this->assertFalse($file->nextChar());
        $this->assertTrue($file->isEOF());
    }

    public function testNotEmptyFile()
    {
        $file = new File(FILES_DIR . 'not_empty_file');        
        $file->nextChar();
        
        $this->assertFalse($file->isEOF());
    }

    public function testFileState()
    {
        $file = new File(FILES_DIR . 'two_lines_file');
        
        $this->assertEquals($file->getCurrentLine(), 1);
        $this->assertEquals($file->getCurrentPos(), 0);

        $file->saveState();
        
        $this->assertEquals($file->nextChar(), "\n");
        $this->assertEquals($file->getCurrentLine(), 2);
        $this->assertEquals($file->getCurrentPos(), 1);

        $file->restoreState();
        
        $this->assertEquals($file->getCurrentLine(), 1);
        $this->assertEquals($file->getCurrentPos(), 0);
        $this->assertEquals($file->nextChar(), "\n");
    }
    
    public function testReadChar()
    {
        $file = new File(FILES_DIR . 'not_empty_file');
        
        $this->assertEquals($file->getCurrentLine(), 1);
        $this->assertEquals($file->getCurrentPos(), 0);

        $this->assertEquals($file->nextChar(), 'n');

        $this->assertEquals($file->getCurrentLine(), 1);
        $this->assertEquals($file->getCurrentPos(), 1);
    }
    
    public function testReadCharMultiLineFileUnix()
    {
        $file = new File(FILES_DIR . 'multiline_file_unix');

        $this->assertEquals($file->getCurrentLine(), 1);
        $this->assertEquals($file->getCurrentPos(), 0);

        $this->assertEquals($file->nextChar(), 1);
        $this->assertEquals($file->nextChar(), "\n");

        $this->assertEquals($file->getCurrentLine(), 2);
        $this->assertEquals($file->getCurrentPos(), 2);

        $this->assertEquals($file->nextChar(), 2);
        $this->assertEquals($file->nextChar(), "\n");

        $this->assertEquals($file->getCurrentLine(), 3);
        $this->assertEquals($file->getCurrentPos(), 4);

        $this->assertEquals($file->nextChar(), "\n");
        $this->assertEquals($file->nextChar(), 3);

        $this->assertEquals($file->getCurrentLine(), 4);
        $this->assertEquals($file->getCurrentPos(), 6);
    }
    
    public function testReadCharMultiLineFileMac()
    {
        $file = new File(FILES_DIR . 'multiline_file_mac');

        $this->assertEquals($file->getCurrentLine(), 1);
        $this->assertEquals($file->getCurrentPos(), 0);

        $this->assertEquals($file->nextChar(), 1);
        $this->assertEquals($file->nextChar(), "\r");

        $this->assertEquals($file->getCurrentLine(), 2);
        $this->assertEquals($file->getCurrentPos(), 2);

        $this->assertEquals($file->nextChar(), 2);
        $this->assertEquals($file->nextChar(), "\r");

        $this->assertEquals($file->getCurrentLine(), 3);
        $this->assertEquals($file->getCurrentPos(), 4);

        $this->assertEquals($file->nextChar(), "\r");
        $this->assertEquals($file->nextChar(), 3);

        $this->assertEquals($file->getCurrentLine(), 4);
        $this->assertEquals($file->getCurrentPos(), 6);
    }
    
    public function testReadCharMultiLineFileWin()
    {
        $file = new File(FILES_DIR . 'multiline_file_win');

        $this->assertEquals($file->getCurrentLine(), 1);
        $this->assertEquals($file->getCurrentPos(), 0);

        $this->assertEquals($file->nextChar(), 1);
        $this->assertEquals($file->nextChar(), "\r");

        $this->assertEquals($file->getCurrentLine(), 1);
        $this->assertEquals($file->getCurrentPos(), 2);

        $this->assertEquals($file->nextChar(), "\n");
        $this->assertEquals($file->nextChar(), 2);

        $this->assertEquals($file->getCurrentLine(), 2);
        $this->assertEquals($file->getCurrentPos(), 4);

        $this->assertEquals($file->nextChar(), "\r");
        $this->assertEquals($file->nextChar(), "\n");

        $this->assertEquals($file->getCurrentLine(), 3);
        $this->assertEquals($file->getCurrentPos(), 6);
        
        $this->assertEquals($file->nextChar(), "\r");
        $this->assertEquals($file->nextChar(), "\n");
        
        $this->assertEquals($file->getCurrentLine(), 4);
        $this->assertEquals($file->getCurrentPos(), 8);
        
        $this->assertEquals($file->nextChar(), 3);
        
        $this->assertEquals($file->getCurrentLine(), 4);
        $this->assertEquals($file->getCurrentPos(), 9);
    }
    
    public function testFileName()
    {
        $file = new File(FILES_DIR . 'empty_file');

        $this->assertEquals(FILES_DIR . 'empty_file', $file->getFileName());
    }
    
    /**
     * @expectedException PHPML\Exception\IOException
     */
    public function testFileDoesNotExist()
    {
        $file = new File(FILES_DIR . 'it_does_not_exist');
    }
    
    public function testRemainingContent()
    {
        $file = new File(FILES_DIR . 'not_empty_file');
        
        $file->nextChar();
        $this->assertEquals('ot_empty_file', $file->readAll());
    }
    
    public function testRewindingNotReadFile()
    {
        $file = new File(FILES_DIR . 'not_empty_file');
        
        $this->assertFalse($file->goBack());
    }
    
    public function testRewindingEmptyFile()
    {
        $file = new File(FILES_DIR . 'empty_file');
        
        $this->assertFalse($file->goBack());
    }
    
    public function testRewindingOneLineFile()
    {
        $file = new File(FILES_DIR . 'not_empty_file');
        
        $this->assertEquals(1, $file->getCurrentLine());
        $this->assertEquals(0, $file->getCurrentPos());
        
        $file->nextChar();        
        $this->assertTrue($file->goBack());
        
        $this->assertEquals(1, $file->getCurrentLine());
        $this->assertEquals(0, $file->getCurrentPos());
    }
    
    public function testRewindingTwoLinesFile()
    {
        $file = new File(FILES_DIR . 'two_lines_file');
        
        $this->assertEquals(1, $file->getCurrentLine());
        $this->assertEquals(0, $file->getCurrentPos());
        
        $file->nextChar();        
        $this->assertTrue($file->goBack());
        
        $this->assertEquals(1, $file->getCurrentLine());
        $this->assertEquals(0, $file->getCurrentPos());
    }
    
    public function testFindOneNeedle()
    {
        $file = new File(FILES_DIR . 'find_1');
        
        $this->assertEquals(8, $file->find('<php:'));
        $this->assertEquals(0, $file->getCurrentPos());
        $this->assertEquals(1, $file->getCurrentLine());
    }
    
    public function testFindMultipleNeedles()
    {
        $file = new File(FILES_DIR . 'find_1');
        
        $this->assertEquals(8, $file->find(array('</div>', '<php:', '</php:')));
        $this->assertEquals(0, $file->getCurrentPos());
        $this->assertEquals(1, $file->getCurrentLine());
    }
    
    public function testCannotFindOneNeedle()
    {
        $file = new File(FILES_DIR . 'find_1');
        
        $this->assertFalse($file->find('<php:Div'));
        $this->assertEquals(0, $file->getCurrentPos());
        $this->assertEquals(1, $file->getCurrentLine());
    }
    
    public function testCannotFindMultipleNeedles()
    {
        $file = new File(FILES_DIR . 'find_1');
        
        $this->assertFalse($file->find(array('</div>', '<ph:', '</ph:')));
        $this->assertEquals(0, $file->getCurrentPos());
        $this->assertEquals(1, $file->getCurrentLine());
    }
}

