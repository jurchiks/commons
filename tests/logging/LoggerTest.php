<?php
namespace js\tools\commons\tests\logging;

use ArgumentCountError;
use js\tools\commons\logging\formatters\LogLevelFormatter;
use js\tools\commons\logging\Logger;
use js\tools\commons\logging\LogLevel;
use js\tools\commons\logging\writers\LogWriter;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
	private static LogWriter $writer;
	
	public static function setUpBeforeClass(): void
	{
		self::$writer = new class implements LogWriter
		{
			public function writeMessage(string $message, int $logLevel): void
			{
				echo $message;
			}
		};
	}
	
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
		
		$logger = new Logger(self::$writer);
		$logger->log($logLevel, 'foo');
	}
	
	/** @dataProvider validLevelNamesDataset */
	public function testLogLevelMethods(int $logLevel, string $name): void
	{
		$this->expectOutputString('foo');
		
		$logger = new Logger(self::$writer);
		$logger->$name('foo');
	}
	
	public function testMissingMessage(): void
	{
		$this->expectException(ArgumentCountError::class);
		$this->expectExceptionMessage('Too few arguments to function');
		
		$logger = new Logger(self::$writer);
		$logger->info();
	}
	
	public function testWithFormatter(): void
	{
		$this->expectOutputString('INFO foo');
		
		$logger = new Logger(self::$writer, new LogLevelFormatter());
		$logger->info('foo');
	}
}
