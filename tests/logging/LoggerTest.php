<?php
namespace js\tools\commons\tests\logging;

use js\tools\commons\logging\Logger;
use js\tools\commons\logging\LogLevel;
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
		yield [LogLevel::DEBUG, 'debug'];
		yield [LogLevel::NOTICE, 'notice'];
		yield [LogLevel::INFO, 'info'];
		yield [LogLevel::WARNING, 'warning'];
		yield [LogLevel::ERROR, 'error'];
		yield [LogLevel::CRITICAL, 'critical'];
		yield [LogLevel::FATAL, 'fatal'];
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
