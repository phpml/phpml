<?php

namespace phpml\tests\parser;

use phpml\lib\parser\token\Token,
    phpml\lib\parser\File;

require_once __DIR__ . '/../bootstrap.php';

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
     * @expectedException phpml\lib\exception\IOException
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
        $this->assertNull($file->goBack());
        
        $this->assertEquals(1, $file->getCurrentLine());
        $this->assertEquals(0, $file->getCurrentPos());
    }
    
    public function testRewindingTwoLinesFile()
    {
        $file = new File(FILES_DIR . 'two_lines_file');
        
        $this->assertEquals(1, $file->getCurrentLine());
        $this->assertEquals(0, $file->getCurrentPos());
        
        $file->nextChar();        
        $this->assertNull($file->goBack());
        
        $this->assertEquals(1, $file->getCurrentLine());
        $this->assertEquals(0, $file->getCurrentPos());
    }
}

