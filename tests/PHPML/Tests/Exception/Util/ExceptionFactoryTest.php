<?php

namespace PHPML\Tests\Exception\Util;

use PHPML\Exception\IOException,
    PHPML\Exception\Util\ExceptionFactory,
    PHPML\Exception\ParserException;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * ExceptionFactory test case
 */
class ExceptionFactoryTest extends \PHPUnit_Framework_TestCase
{
	public function testCreateUnexpectedCharException()
	{	
		$parserException = new ParserException('source.php', 8, 'Unexpected char < in source.phpml on line 5');
		$exception		 = ExceptionFactory::createUnexpectedChar('source.php', 8, 'source.phpml', 5, '<');
		
		$this->assertSame($parserException->getFile(), $exception->getFile());
		$this->assertSame($parserException->getLine(), $exception->getLine());
		$this->assertSame($parserException->getMessage(), $exception->getMessage());
		$this->assertInstanceOf('PHPML\Exception\ParserException', $exception);	
	}
	
	public function testCreateCannotFindCharException()
	{
		$parserException = new ParserException('source.php', 8, 'Cannot find char : in source.phpml on line 5');
		$exception		 = ExceptionFactory::createCannotFindChar('source.php', 8, 'source.phpml', 5, ':');
	
		$this->assertSame($parserException->getFile(), $exception->getFile());
		$this->assertSame($parserException->getLine(), $exception->getLine());
		$this->assertSame($parserException->getMessage(), $exception->getMessage());
		$this->assertInstanceOf('PHPML\Exception\ParserException', $exception);
	}
	
	public function testCreateIllegalSpaceException()
	{
		$parserException = new ParserException('source.php', 8, 'Illegal space found in source.phpml on line 5');
		$exception		 = ExceptionFactory::createIllegalSpace('source.php', 8, 'source.phpml', 5);
		
		$this->assertSame($parserException->getFile(), $exception->getFile());
		$this->assertSame($parserException->getLine(), $exception->getLine());
		$this->assertSame($parserException->getMessage(), $exception->getMessage());
		$this->assertInstanceOf('PHPML\Exception\ParserException', $exception);
	}
	
	public function testCreateOpenFileException()
	{
		$ioException = new IOException('source.php', 8, 'Cannot open file source.phpml for reading');
		$exception	 = ExceptionFactory::createOpenFile('source.php', 8, 'source.phpml', 'reading');
		
		$this->assertSame($ioException->getFile(), $exception->getFile());
		$this->assertSame($ioException->getLine(), $exception->getLine());
		$this->assertSame($ioException->getMessage(), $exception->getMessage());
		$this->assertInstanceOf('PHPML\Exception\IOException', $exception);
	}

	public function testCreateUnexpectedEOFException()
	{
		$parserException = new ParserException('source.php', 8, 'Unexpected end of file in source.phpml on line 5');	
		$exception		 = ExceptionFactory::createUnexpectedEOF('source.php', 8, 'source.phpml', 5);
		
		$this->assertSame($parserException->getFile(), $exception->getFile());
		$this->assertSame($parserException->getLine(), $exception->getLine());
		$this->assertSame($parserException->getMessage(), $exception->getMessage());
		$this->assertInstanceOf('PHPML\Exception\ParserException', $exception);		
	}
	
	public function testCreateFileDoesNotExistException()
	{
	    $ioException = new IOException('source.php', 8, 'File: source.phpml doesn\'t exist');
	    $exception   = ExceptionFactory::createFileDoesNotExist('source.php', 8, 'source.phpml');
	    
	    $this->assertSame($ioException->getFile(), $exception->getFile());
	    $this->assertSame($ioException->getLine(), $exception->getLine());
	    $this->assertSame($ioException->getMessage(), $exception->getMessage());
	    $this->assertInstanceOf('PHPML\Exception\IOException', $exception);
	}

}
