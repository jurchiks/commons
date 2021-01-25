<?php
namespace js\tools\commons\tests\logging;

use js\tools\commons\exceptions\LogException;
use js\tools\commons\logging\Logger;
use PHPUnit\Framework\TestCase;

class TestLogger extends Logger
{
	protected function write(string $message, int $level): void
	{
		echo $message;
	}
}

class LoggerTest extends TestCase
{
	public function validLevelNamesDataset(): iterable
	{
		yield [Logger::DEBUG, 'debug'];
		yield [Logger::NOTICE, 'notice'];
		yield [Logger::INFO, 'info'];
		yield [Logger::WARNING, 'warning'];
		yield [Logger::ERROR, 'error'];
		yield [Logger::CRITICAL, 'critical'];
		yield [Logger::FATAL, 'fatal'];
	}
	
	/** @dataProvider validLevelNamesDataset */
	public function testValidLevelNames(int $logLevel, string $name): void
	{
		$this->assertSame($name, Logger::getLevelName($logLevel));
	}
	
	public function testInvalidLevelName(): void
	{
		$this->expectException(LogException::class);
		$this->expectExceptionMessage('Invalid log level: ' . PHP_INT_MAX);
		
		Logger::getLevelName(PHP_INT_MAX);
	}
	
	/** @dataProvider validLevelNamesDataset */
	public function testLog(int $logLevel): void
	{
		$this->expectOutputString('foo');
		
		$logger = new TestLogger();
		$logger->log($logLevel, 'foo');
	}
	
	/** @dataProvider validLevelNamesDataset */
	public function testLogLevelMethods(int $logLevel, string $name): void
	{
		$this->expectOutputString('foo');
		
		$logger = new TestLogger();
		$logger->$name('foo');
	}
	
	public function testMessageFormatting(): void
	{
		$this->expectOutputString('foo bar baz');
		
		$logger = new TestLogger();
		$logger->info('foo %s %s', 'bar', 'baz');
	}
}
