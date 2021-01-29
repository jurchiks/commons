<?php
namespace js\tools\commons\tests\logging\writers;

use js\tools\commons\exceptions\LogException;
use js\tools\commons\logging\LogLevel;
use js\tools\commons\logging\writers\FileWriter;
use PHPUnit\Framework\TestCase;

class FileWriterTest extends TestCase
{
	public static function tearDownAfterClass(): void
	{
		foreach (glob(__DIR__ . '/*.log') as $logFile)
		{
			unlink($logFile);
		}
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
	
	/** @dataProvider validLevelNamesDataset() */
	public function testWriteToFile(int $logLevel, string $levelName): void
	{
		$writer = new FileWriter(__DIR__);
		$writer->writeMessage('foo', $logLevel);
		
		$logFile = __DIR__ . '/' . $levelName . '.log';
		$this->assertFileExists($logFile);
		$this->assertStringEqualsFile($logFile, 'foo' . PHP_EOL);
	}
	
	public function testInvalidLogLevel(): void
	{
		$this->expectException(LogException::class);
		
		$writer = new FileWriter(__DIR__);
		$writer->writeMessage('foo', PHP_INT_MAX);
	}
}
