<?php
namespace js\tools\commons\tests\logging\formatters;

use js\tools\commons\exceptions\LogException;
use js\tools\commons\logging\formatters\LogLevelFormatter;
use js\tools\commons\logging\LogLevel;
use PHPUnit\Framework\TestCase;

class LogLevelFormatterTest extends TestCase
{
	public function validLevelNamesDataset(): iterable
	{
		yield [LogLevel::DEBUG, 'DEBUG'];
		yield [LogLevel::NOTICE, 'NOTICE'];
		yield [LogLevel::INFO, 'INFO'];
		yield [LogLevel::WARNING, 'WARNING'];
		yield [LogLevel::ERROR, 'ERROR'];
		yield [LogLevel::CRITICAL, 'CRITICAL'];
		yield [LogLevel::FATAL, 'FATAL'];
	}
	
	/** @dataProvider validLevelNamesDataset() */
	public function testFormatMessage(int $logLevel, string $levelName): void
	{
		$formatter = new LogLevelFormatter();
		
		$this->assertSame($levelName . ' foo', $formatter->getFormattedMessage('foo', $logLevel));
	}
	
	public function testInvalidLogLevel(): void
	{
		$this->expectException(LogException::class);
		
		$logger = new LogLevelFormatter();
		$logger->getFormattedMessage('foo', PHP_INT_MAX);
	}
}
