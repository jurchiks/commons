<?php
namespace js\tools\commons\tests\logging;

use js\tools\commons\exceptions\LogException;
use js\tools\commons\logging\LogLevel;
use PHPUnit\Framework\TestCase;

class LogLevelTest extends TestCase
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
	public function testValidLevels(int $logLevel, string $name): void
	{
		$this->assertSame($name, LogLevel::getName($logLevel));
	}
	
	public function testInvalidLevel(): void
	{
		$invalidLevel = PHP_INT_MAX;
		
		$this->expectException(LogException::class);
		$this->expectExceptionMessage('Invalid log level: ' . $invalidLevel);
		
		LogLevel::getName($invalidLevel);
	}
	
	/** @dataProvider validLevelNamesDataset */
	public function testValidLevelNames(int $logLevel, string $name): void
	{
		$this->assertSame($logLevel, LogLevel::getByName($name));
	}
	
	public function testInvalidLevelName(): void
	{
		$invalidName = 'nonsense';
		
		$this->expectException(LogException::class);
		$this->expectExceptionMessage('Invalid log level name: ' . $invalidName);
		
		LogLevel::getByName($invalidName);
	}
}
